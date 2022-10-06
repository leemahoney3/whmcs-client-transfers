<?php

namespace LMTech\ClientTransfers\Handlers;

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
 * @version    1.0.2
 * @link       https://leemahoney.dev
 */


/*

So.. there is a slight issue with regenerating invoices...

WHMCS wont generate the invoice again if nothing has changed since the last generation. What I mean is if an invoice was generated for the service on client A's account, it will only regenerate if the due date changes on client B's account.

Possible solution: completely delete the invoice and related invoice items? Move non-related services to another invoice first however or just delete the items? Will try this.

Another possible solution: change the date on the services by one day and then generate the invoices without sending the email. Modify the invoices back to the correct dates as well as the services and then send out the emails for the invoices.

^ thanks WHMCS, a simple override would have been nice... will try solution one first.

*/

use WHMCS\Service\Addon;
use WHMCS\Billing\Invoice as InvoiceModel;
use WHMCS\Billing\Invoice\Item as InvoiceItem;

use LMTech\ClientTransfers\Models\Log;
use LMTech\ClientTransfers\Handlers\Email;
use LMTech\ClientTransfers\Database\Database;

class Invoice {

    public static function generateDue($clientID) {

        $result = localAPI('GenInvoices', [
            'clientid' => $clientID
        ]);

        if ($result['result'] == 'success') {
            Log::add('invoice', 'success', 'Successfully generated due invoices for client: ' . json_encode($result), $clientID);
        } else {
            Log::add('invoice', 'error', 'Unable to generate due invoices for client: ' . json_encode($result), $clientID);
        }

    }

    public static function cancel($type, $serviceID, $clientID) {

        $clientInvoices = InvoiceModel::where('userid', $clientID)->where('status', 'Unpaid')->get();

        # Loop through all invoices for client
        foreach ($clientInvoices as $invoice) {

            $related    = [];
            $unrelated  = [];
            $count      = 0;

            $invoiceItems = InvoiceItem::where('invoiceid', $invoice->id)->get();

            # On each invoice, count the invoice items (to get a total)
            $itemCount = count($invoiceItems);
            
            # loop through each invoice item for that invoice and check if its related (if it is, add a count +1) (and add it to a related array)
            foreach ($invoiceItems as $item) {

                if ($type == 'service') {

                    $addonIds = [];

                    $getServiceAddons = Addon::where('hostingid', $serviceID)->get();

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
                InvoiceModel::where('id', $invoice->id)->update([
                    'status' => 'Cancelled'
                ]);

                # Workaround for due invoices not being generated. Change the relid on each invoice item to a non-existant one so the invoice regenerates if for some reason the client gets the service transferred back to them
                foreach ($related as $itemID) {

                    $item = InvoiceItem::where('id', $itemID)->first();

                    InvoiceItem::where('id', $itemID)->update([
                        'relid'         => 0,
                        'description'   => '(Transfered Away) ' . $item->description
                    ]);

                }

            } else {
                
                # if they do not match, delete each invoice item based on their id and send an updated invoice email notification
                foreach ($related as $itemID) {
                    InvoiceItem::where('id', $itemID)->delete();
                }

                self::updateTotal($invoice);

                Email::send('Invoice Modified', $invoice->id);
            
            }

        }

    } 

    public static function updateTotal($invoice) {

        $invoiceItems = InvoiceItem::where('invoiceid', $invoice->id)->get();

        $subTotal   = 0;
        $total      = 0;

        foreach ($invoiceItems as $item) {
            $subTotal += $item->amount;
        }

        $subTotal = number_format($subTotal, 2, '.', '');

        $tax    = $subTotal * $invoice->taxrate / 100;
        $tax2   = $subTotal * $invoice->taxrate2 / 100;
        $total  = ($subTotal + $tax + $tax2) - $invoice->credit;

        InvoiceModel::where('id', $invoice->id)->update([
            'subTotal'  => $subTotal,
            'tax'       => $tax,
            'tax2'      => $tax2,
            'total'     => $total
        ]);

    }

}