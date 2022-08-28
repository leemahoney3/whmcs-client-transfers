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

class Transfer {

    public static function create($clientID, $recipientEmail, $type, $id) {
        
        $currentClient = Database::getClientById($clientID);
        $gainingClient = Database::getClientByEmail($recipientEmail);

        $token = bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));

        Database::insert([
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

        if ($type == 'domain') {
            $service_domain = Database::getDomainById($id)->domain;
        } else {
            $service_domain = Database::getServiceById($id)->name . ' for ' . Database::getServiceById($id)->domain;
        }

        $url = Setting::getValue('SystemURL') . '/index.php?m=clienttransfers';

        Email::send('CT_Service Transfer Request Submitted - Losing Client', $currentClient->id, [
            'gaining_client_email'  => $recipientEmail,
            'service_type'          => $type,
            'service_domain'        => $service_domain
        ]);

        Email::send('CT_New Service Transfer Request - Gaining Client', $gainingClient->id, [
            'losing_client_name'    => $currentClient->firstname . ' ' . $currentClient->lastname,
            'losing_client_email'   => $currentClient->email,
            'service_type'          => $type,
            'service_domain'        => $service_domain,
            'accept_link'           => $url . '&action=accept&token=' . $token,
            'deny_link'             => $url . '&action=deny&token=' . $token
        ]);


    }

    public static function cancel($id) {

        Database::update($id, [
            'status'        => 'cancelled',
            'completed_at'  => date('Y-m-d H:i:s'),
            'token'         => ''
        ]);

        $transferDetails = Transfer::getById($id);

        if ($type == 'domain') {
            $service_domain = Database::getDomainById($transferDetails->domain_id)->domain;
        } else {
            $service_domain = Database::getServiceById($transferDetails->service_id)->name . ' for ' . Database::getServiceById($transferDetails->service_id)->domain;
        }

        Email::send('CT_Service Transfer Request Cancelled - Losing Client', $transferDetails->losing_client_id, [
            'gaining_client_email'  => $transferDetails->gaining_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        Email::send('CT_Service Transfer Request Cancelled - Gaining Client', $transferDetails->gaining_client_id, [
            'losing_client_name'    => $transferDetails->losing_client_name,
            'losing_client_email'   => $transferDetails->losing_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

    }

    public static function deny($id) {

        Database::update($id, [
            'status'        => 'denied',
            'completed_at'  => date('Y-m-d H:i:s'),
            'token'         => ''
        ]);

        $transferDetails = Transfer::getById($id);

        if ($type == 'domain') {
            $service_domain = Database::getDomainById($transferDetails->domain_id)->domain;
        } else {
            $service_domain = Database::getServiceById($transferDetails->service_id)->name . ' for ' . Database::getServiceById($transferDetails->service_id)->domain;
        }

        
        // die(print_r([
        //     'losing_client_id'      => $transferDetails->losing_client_id,
        //     'gaining_client_email'  => $transferDetails->gaining_client_email,
        //     'service_type'          => $transferDetails->type,
        //     'service_domain'        => $service_domain,
        //     'gaining_client_id'     => $transferDetails->gaining_client_id,
        //     'losing_client_name'    => $transferDetails->losing_client_name,
        //     'losing_client_email'   => $transferDetails->losing_client_email,
        //     'service_type'          => $transferDetails->type,
        //     'service_domain'        => $service_domain
        // ]));

        Email::send('CT_Service Transfer Request Denied - Losing Client', $transferDetails->losing_client_id, [
            'gaining_client_email'  => $transferDetails->gaining_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        Email::send('CT_Service Transfer Request Denied - Gaining Client', $transferDetails->gaining_client_id, [
            'losing_client_name'    => $transferDetails->losing_client_name,
            'losing_client_email'   => $transferDetails->losing_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

    }



    public static function accept($id) {

        $transferDetails = self::getById($id);

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

        Database::update($id, [
            'status'        => 'accepted',
            'completed_at'  => date('Y-m-d H:i:s'),
            'token'         => ''
        ]);

        

        # Generate new invoices on the gaining clients account
        Invoice::generateDue($transferDetails->gaining_client_id);

        Email::send('CT_Service Transfer Request Completed - Losing Client', $transferDetails->losing_client_id, [
            'gaining_client_email'  => $transferDetails->gaining_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

        Email::send('CT_Service Transfer Request Completed - Gaining Client', $transferDetails->gaining_client_id, [
            'losing_client_name'    => $transferDetails->losing_client_name,
            'losing_client_email'   => $transferDetails->losing_client_email,
            'service_type'          => $transferDetails->type,
            'service_domain'        => $service_domain
        ]);

    }

    public static function getById($id) {

        return Database::getTableById($id);

    }

    public static function getByToken($token) {
        return Database::getTableByField('token', $token);
    }

    public static function checkRelationshipExists($clientID, $recipientID, $type, $id, $status) {
        return Database::getTransfersByRelationship($clientID, $recipientID, $type, $id, $status);
    }

    public static function getPending($clientID) {

        $pendingTransfers = [];
                
        $getPendingTransfers = Database::getTransfersByClientID('losing_client_id', $clientID, ['pending']);

        foreach ($getPendingTransfers as $key => $pendingTransfer) {

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

    public static function getPrevious($clientID) {

        $previousTransfers = [];
                
        $getPreviousTransfers = Database::getTransfersByClientID('losing_client_id', $clientID, ['accepted', 'denied', 'cancelled']);
        

        foreach ($getPreviousTransfers as $key => $previousTransfer) {

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

    public static function getIncoming($clientID) {

        $incomingRequests = [];
                
        $getIncomingRequests = Database::getTransfersByClientID('gaining_client_id', $clientID, ['pending']);

        foreach ($getIncomingRequests as $key => $incomingRequest) {

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

    public static function countIncoming($clientID) {
        return count(self::getIncoming($clientID));
    }

    public static function getPreviousRequests($clientID) {

        $previousRequests = [];
                
        $getPreviousRequests = Database::getTransfersByClientID('gaining_client_id', $clientID, ['denied', 'accepted']);

        foreach ($getPreviousRequests as $key => $previousRequest) {

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