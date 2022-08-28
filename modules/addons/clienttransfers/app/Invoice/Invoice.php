<?php

namespace LMTech\ClientTransfers\Invoice;

/**
 * WHMCS Client Transfers
 *
 * Allow clients to transfer services and domains internally, a badly needed feature.
 * 
 * !! Warning: This is a work in progress, please do not download yet for production. !! 
 *
 * @package    WHMCS
 * @author     Lee Mahoney <lee@leemahoney.dev>
 * @copyright  Copyright (c) Lee Mahoney 2022
 * @license    MIT License
 * @version    1.0.0
 * @link       https://leemahoney.dev
 */

use LMTech\ClientTransfers\Email\Email;
use LMTech\ClientTransfers\Logger\Logger;
use LMTech\ClientTransfers\Database\Database;

class Invoice {

    public static function generateDue($clientID) {

        $result = localAPI('GenInvoices', [
            'clientid' => $clientID
        ]);

        if ($result['result'] == 'success') {
            Logger::add('invoice', 'success', 'Successfully generated due invoices for client: ' . json_encode($result), $clientID);
        } else {
            Logger::add('invoice', 'error', 'Unable to generate due invoices for client: ' . json_encode($result), $clientID);
        }

    }

    public static function cancel($type, $serviceID, $clientID) {

        $clientInvoices = Database::getClientInvoices($clientID);

        # Loop through all invoices for client
        foreach ($clientInvoices as $invoice) {

            $related    = [];
            $unrelated  = [];
            $count      = 0;

            $invoiceItems = Database::getInvoiceItems($invoice->id);

            # On each invoice, count the invoice items (to get a total)
            $itemCount = count($invoiceItems);
            
            # loop through each invoice item for that invoice and check if its related (if it is, add a count +1) (and add it to a related array)
            foreach ($invoiceItems as $item) {

                if ($type == 'service') {

                    $addonIds = [];

                    $getServiceAddons = Database::getServiceAddons($serviceID);

                    foreach ($getServiceAddons as $addon) {
                        $addonIds[] = $addon->id;
                    }

                    if ($item->relid == $serviceID || in_array($item->relid, $addonIds)) {
                        $count++;
                        $related[] = $item->id;
                    } else {
                        # check also for unrelated and add to an unrelated array
                        $unrelated[] = $item->id;
                    }

                } else {

                    if ($item->relid == $serviceID) {
                        $count++;
                        $related[] = $item->id;
                    } else {
                        # check also for unrelated and add to an unrelated array
                        $unrelated[] = $item->id;
                    }

                }

            }

            # do a check on count vs invoice items count, if they do match, then just cancel the invoice
            if ($itemCount == $count) {
                
                # Cancel the invoice
                Database::cancelInvoice($invoice->id);

                # Workaround for due invoices not being generated. Change the relid on each invoice item to a non-existant one so the invoice regenerates if for some reason the client gets the service transferred back to them
                foreach ($related as $itemID) {
                    $item = Database::getInvoiceItem($itemID);
                    Database::updateInvoiceItem($itemID, [
                        'relid' => 0,
                        'description' => '(Transfered Away) ' . $item->description
                    ]);
                }

            } else {
                
                # if they do not match, delete each invoice item based on their id and send an updated invoice email notification
                foreach ($related as $itemID) {
                    Database::deleteInvoiceItem($itemID);
                }

                self::updateTotal($invoice);

                Email::send('Invoice Modified', $invoice->id);
            
            }

        }

    } 

    public static function updateTotal($invoice) {

        $invoiceItems = Database::getInvoiceItems($invoice->id);

        $subTotal   = 0;
        $total      = 0;

        foreach ($invoiceItems as $item) {

            $itemData = Database::getInvoiceItem($item->id);

            $subTotal += $itemData->amount;

        }

        $subTotal = number_format($subTotal, 2, '.', '');

        $tax = $subTotal * $invoice->taxrate / 100;
        $tax2 = $subTotal * $invoice->taxrate2 / 100;

        $total = ($subTotal + $tax + $tax2) - $invoice->credit;

        Database::updateInvoice($invoice->id, [
            'subTotal' => $subTotal,
            'tax' => $tax,
            'tax2' => $tax2,
            'total' => $total
        ]);

    }

}