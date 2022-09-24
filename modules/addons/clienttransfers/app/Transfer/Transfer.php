<?php

namespace LMTech\ClientTransfers\Transfer;

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

use WHMCS\Config\Setting;
use LMTech\ClientTransfers\Email\Email;
use LMTech\ClientTransfers\Invoice\Invoice;
use LMTech\ClientTransfers\Database\Database;
use LMTech\ClientTransfers\Transfer\Transfer;
use LMTech\ClientTransfers\Helpers\SubscriptionHelper;

class Transfer {

    # Create a transfer
    public static function create($clientID, $recipientEmail, $type, $id) {
        
        # Grab current and gaining client details
        $currentClient = \WHMCS\User\Client::where('id', $clientID)->first();
        $gainingClient = \WHMCS\User\Client::where('id', $recipientEmail)->first();

        # Generate a random transfer token for acceptance through email
        $token = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));

        # Insert details into the transfers table
        TransferMode::insert([
            'losing_client_id'      => $clientID,
            'losing_client_name'    => $currentClient->firstname . ' ' . $currentClient->lastname,
            'losing_client_email'   => $currentClient->email,
            'gaining_client_id'     => $gainingClient->id,
            'gaining_client_email'  => $recipientEmail,
            'type'                  => $type,
            'service_id'            => ($type == 'service') ? $id : '',
            'domain_id'             => ($type == 'domain') ? $id : '',
            'requested_at'          => date('Y-m-d H:i:s'),
            'completed_at'          => '',
            'status'                => 'pending',
            'token'                 => $token
        ]);

        # Get the service/domain for the email
        if ($type == 'domain') {
            $service_domain = \WHMCS\Domain\Domain::where('id', $id)->first()->domain;
        } else {
            $service_domain = \WHMCS\Service\Service::where('id', $id)->first()->name . ' for ' . \WHMCS\Service\Service::where('id', $id)->first()->domain;
        }

        # Get the client area link
        $url = Setting::getValue('SystemURL') . '/index.php?m=clienttransfers';

        # Send Email to the losing client
        Email::send('CT_Service Transfer Request Submitted - Losing Client', $currentClient->id, [
            'gaining_client_email'  => $recipientEmail,
            'service_type'          => $type,
            'service_domain'        => $service_domain
        ]);

        # Send Email to the gaining client
        Email::send('CT_New Service Transfer Request - Gaining Client', $gainingClient->id, [
            'losing_client_name'    => $currentClient->firstname . ' ' . $currentClient->lastname,
            'losing_client_email'   => $currentClient->email,
            'service_type'          => $type,
            'service_domain'        => $service_domain,
            'accept_link'           => $url . '&action=accept&token=' . $token,
            'deny_link'             => $url . '&action=deny&token=' . $token
        ]);


    }

    # Cancel a transfer
    public static function cancel($id) {

        # Update the transfers table
        Database::update($id, [
            'status'        => 'cancelled',
            'completed_at'  => date('Y-m-d H:i:s'),
            'token'         => ''
        ]);

        # Get the transfer details
        $transferDetails = Transfer::getById($id);

        # Get the service/domain for the email
        if ($type == 'domain') {
            $service_domain = Database::getDomainById($transferDetails->domain_id)->domain;
        } else {
            $service_domain = Database::getServiceById($transferDetails->service_id)->name . ' for ' . Database::getServiceById($transferDetails->service_id)->domain;
        }

        # Send Email to the losing client
        Email::send('CT_Service Transfer Request Cancelled - Losing Client', $transferDetails->losing_client_id, [
            'gaining_client_email'  => $transferDetails->gaining_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        # Send Email to the gaining client
        Email::send('CT_Service Transfer Request Cancelled - Gaining Client', $transferDetails->gaining_client_id, [
            'losing_client_name'    => $transferDetails->losing_client_name,
            'losing_client_email'   => $transferDetails->losing_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

    }

    # Deny the transfer request
    public static function deny($id) {

        # Update the transfers table
        Database::update($id, [
            'status'        => 'denied',
            'completed_at'  => date('Y-m-d H:i:s'),
            'token'         => ''
        ]);

        # Get the transfer details
        $transferDetails = Transfer::getById($id);

        # Get the service/domain for the email
        if ($type == 'domain') {
            $service_domain = Database::getDomainById($transferDetails->domain_id)->domain;
        } else {
            $service_domain = Database::getServiceById($transferDetails->service_id)->name . ' for ' . Database::getServiceById($transferDetails->service_id)->domain;
        }

        # Send Email to the losing client
        Email::send('CT_Service Transfer Request Denied - Losing Client', $transferDetails->losing_client_id, [
            'gaining_client_email'  => $transferDetails->gaining_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        # Send Email to the gaining client
        Email::send('CT_Service Transfer Request Denied - Gaining Client', $transferDetails->gaining_client_id, [
            'losing_client_name'    => $transferDetails->losing_client_name,
            'losing_client_email'   => $transferDetails->losing_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

    }

    # Accept the transfer request
    public static function accept($id) {

        # Grab the transfer details
        $transferDetails = self::getById($id);

        # Check type and run relevant updates (e.g. reassign addons, handle invoices)
        if ($transferDetails->type == 'service') {

            # Still need to handle invoices!
            Database::updateServiceToNewClient($transferDetails->service_id, $transferDetails->gaining_client_id);
            Database::updateServiceAddonsToClient($transferDetails->service_id, $transferDetails->gaining_client_id);

            # Cancel related invoices on current account
            Invoice::cancel('service', $transferDetails->service_id, $transferDetails->losing_client_id);

        } else if ($transferDetails->type == 'domain') {

            Database::updateDomainToNewClient($transferDetails->domain_id, $transferDetails->gaining_client_id);

            # Cancel related invoices on current account
            Invoice::cancel('domain', $transferDetails->domain_id, $transferDetails->losing_client_id);

        }

        # Cancel any subscription related to the service
        $serviceDetails = Database::getServiceById($transferDetails->service_id);

        if (!empty($serviceDetails->subscriptionid)) {
            SubscriptionHelper::cancel($serviceDetails);
        }

        # Update the transfers table
        Database::update($id, [
            'status'        => 'accepted',
            'completed_at'  => date('Y-m-d H:i:s'),
            'token'         => ''
        ]);

        

        # Generate new invoices on the gaining clients account
        Invoice::generateDue($transferDetails->gaining_client_id);

        # Send Email to the losing client
        Email::send('CT_Service Transfer Request Completed - Losing Client', $transferDetails->losing_client_id, [
            'gaining_client_email'  => $transferDetails->gaining_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        # Send Email to the gaining client
        Email::send('CT_Service Transfer Request Completed - Gaining Client', $transferDetails->gaining_client_id, [
            'losing_client_name'    => $transferDetails->losing_client_name,
            'losing_client_email'   => $transferDetails->losing_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        Email::sendWelcomeEmail($serviceDetails);

    }

    # Get transfer requests by the ID provided
    public static function getById($id) {
        return Database::getTableById($id);
    }

    # Get transfer requests by the token provided
    public static function getByToken($token) {
        return Database::getTableByField('token', $token);
    }

    # Check if a relationship exists (i.e. to check for a valid transfer)
    public static function checkRelationshipExists($clientID, $recipientID, $type, $id, $status) {
        return Database::getTransfersByRelationship($clientID, $recipientID, $type, $id, $status);
    }

    # Count incoming transfer requests based on the client's ID
    public static function countIncoming($clientID) {
        return count(Database::getTransfersByClientID('gaining_client_id', $clientID, ['pending']));
    }

    # Format the pending transfer requests data from the database for the table in the client area
    public static function outputPending($data) {

        $pendingTransfers = [];

        foreach ($data as $key => $pendingTransfer) {

            $pendingTransfers[$key] = [
                'id'                => $pendingTransfer->id,
                'type'              => $pendingTransfer->type,
                'target_account'    => $pendingTransfer->gaining_client_email,
                'requested'         => date('d/m/Y @ H:i:s', strtotime($pendingTransfer->requested_at))
            ];

            if ($pendingTransfer->type == 'domain') {
                $pendingTransfers[$key]['domain'] = Database::getDomainById($pendingTransfer->domain_id)->domain;
            } else {
                $pendingTransfers[$key]['service'] = Database::getServiceById($pendingTransfer->service_id)->name . ' for ' . Database::getServiceById($pendingTransfer->service_id)->domain;
            }

        }

        return $pendingTransfers;

    }

    # Format the previous transfers data from the database for the table in the client area
    public static function outputPrevious($data) {

        $previousTransfers = [];

        foreach ($data as $key => $previousTransfer) {

            $previousTransfers[$key] = [
                'id'                => $previousTransfer->id,
                'type'              => $previousTransfer->type,
                'target_account'    => $previousTransfer->gaining_client_email,
                'status'            => self::formatStatus($previousTransfer->status),
                'requested'         => date('d/m/Y @ H:i:s', strtotime($previousTransfer->requested_at)),
                'completed'         => date('d/m/Y @ H:i:s', strtotime($previousTransfer->completed_at))
            ];

            if ($previousTransfer->type == 'domain') {
                $previousTransfers[$key]['domain'] = Database::getDomainById($previousTransfer->domain_id)->domain;
            } else {
                $previousTransfers[$key]['service'] = Database::getServiceById($previousTransfer->service_id)->name . ' for ' . Database::getServiceById($previousTransfer->service_id)->domain;
            }

        }

        return $previousTransfers;

    }

    # Format the incoming requests data from the database for the table in the client area
    public static function outputIncoming($data) {

        $incomingRequests = [];

        foreach ($data as $key => $incomingRequest) {

            $incomingRequests[$key] = [
                'id'                => $incomingRequest->id,
                'type'              => $incomingRequest->type,
                'requested_by'      => $incomingRequest->losing_client_name . ' - ' . $incomingRequest->losing_client_email,
                'requested_date'    => date('d/m/Y @ H:i:s', strtotime($incomingRequest->requested_at))
            ];

            if ($incomingRequest->type == 'domain') {
                $incomingRequests[$key]['domain'] = Database::getDomainById($incomingRequest->domain_id)->domain;
            } else {
                $incomingRequests[$key]['service'] = Database::getServiceById($incomingRequest->service_id)->name . ' for ' . Database::getServiceById($incomingRequest->service_id)->domain;
            }

        }

        return $incomingRequests;
        
    }

    # Format the previous requests data from the database for the table in the client area
    public static function outputPreviousRequests($data) {

        $previousRequests = [];

        foreach ($data as $key => $previousRequest) {

            $previousRequests[$key] = [
                'id'                => $previousRequest->id,
                'type'              => $previousRequest->type,
                'requested_by'      => $previousRequest->losing_client_name . ' - ' . $previousRequest->losing_client_email,
                'requested_date'    => date('d/m/Y @ H:i:s', strtotime($previousRequest->requested_at)),
                'completed_date'    => date('d/m/Y @ H:i:s', strtotime($previousRequest->completed_at)),
                'result'            => self::formatStatus($previousRequest->status)
            ];

            if ($previousRequest->type == 'domain') {
                $previousRequests[$key]['domain'] = Database::getDomainById($previousRequest->domain_id)->domain;
            } else {
                $previousRequests[$key]['service'] = Database::getServiceById($previousRequest->service_id)->name . ' for ' . Database::getServiceById($previousRequest->service_id)->domain;
            }

        }

        return $previousRequests;

    }

    # Get transfers by their status (for admin dashboard)
    public static function getByStatus($type, $status) {

        if ($type == 'transfer') {
            
            $transfers = [];

            $getTransfers = Database::getTransfers($status);

            foreach ($getTransfers as $key => $transfer) {

                $transfers[$key] = [
                    'id' => $transfer->id,
                    'type' => $transfer->type,
                    'losing_client' => Database::getClientById($transfer->losing_client_id),
                    'gaining_client' => Database::getClientById($transfer->gaining_client_id),
                    'requested_at' => date('d/m/Y @ H:i:s', strtotime($transfer->requested_at)),
                    'completed_at' => date('d/m/Y @ H:i:s', strtotime($transfer->requested_at)),
                    'service_domain' => ($transfer->type == 'domain') ? 'Domain - ' . Database::getDomainById($transfer->domain_id)->domain : Database::getServiceById($transfer->service_id)->name . ' - ' . Database::getServiceById($transfer->service_id)->domain,
                    'service_id' => $transfer->service_id,
                    'domain_id' => $transfer->domain_id
                ];

            }

            return $transfers;
            
        } else if ($type == 'service') {
            return Database::getTransfers($status, 'service');
        } else if ($type == 'domain') {
            return Database::getTransfers($status, 'domain');
        }

    }

    public static function outputByStatus($data) {

        $transfers = [];

        foreach ($data as $key => $transfer) {

            $transfers[$key] = [
                'id' => $transfer->id,
                'type' => $transfer->type,
                'losing_client' => Database::getClientById($transfer->losing_client_id),
                'gaining_client' => Database::getClientById($transfer->gaining_client_id),
                'requested_at' => date('d/m/Y @ H:i:s', strtotime($transfer->requested_at)),
                'completed_at' => date('d/m/Y @ H:i:s', strtotime($transfer->requested_at)),
                'service_domain' => ($transfer->type == 'domain') ? 'Domain - ' . Database::getDomainById($transfer->domain_id)->domain : Database::getServiceById($transfer->service_id)->name . ' - ' . Database::getServiceById($transfer->service_id)->domain,
                'service_domain_link' => ($transfer->type == 'domain') ? "clientsdomains.php?userid=" . Database::getDomainById($transfer->domain_id)->userid . "&id={$transfer->domain_id}" : "clientsservices.php?userid=" . Database::getServiceById($transfer->service_id)->userid . "&id={$transfer->service_id}",
                'service_id' => $transfer->service_id,
                'domain_id' => $transfer->domain_id
            ];

        }

        return $transfers;

    }

    # Return the status in a formatted label
    protected static function formatStatus($status) {

        switch ($status) {
            case 'pending':
                return '<span class="label label-warning">Pending</span>';
                break;
            
            case 'accepted':
                return '<span class="label label-success">Accepted</span>';
                break;

            case 'denied':
                return '<span class="label label-danger">Denied</span>';
                break;

            case 'cancelled':
                return '<span class="label label-default">Cancelled</span>';
                break;
        }

    }

}