<?php

namespace LMTech\ClientTransfers\Client;

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

use LMTech\ClientTransfers\Config\Config;
use LMTech\ClientTransfers\Logger\Logger;
use LMTech\ClientTransfers\Client\ClientPage;
use LMTech\ClientTransfers\Database\Database;
use LMTech\ClientTransfers\Transfer\Transfer;
use LMTech\ClientTransfers\Models\TransferModel;

class Client {

    public static function output($vars) {

        $clientID   = $_SESSION['uid'];
        $page       = new ClientPage($clientID);

        switch ($page->getAction()) {

            case 'init':
                
                if (isset($_POST['init_submit'])) {
                    
                    // Grab a few variables
                    $serviceDomain  = $page->getPost('servicedomain');
                    $recipientEmail = $page->getPost('recipientemail');

                    $type   = (explode('_', $serviceDomain)[0] == 'd') ? 'domain' : 'service';
                    $id     = explode('_', $serviceDomain)[1];

                    if (empty($serviceDomain) || empty($recipientEmail)) {

                        $page->setPage('init');

                        $page->setVar('message', [
                            'type'      => 'error',
                            'content'   => 'Whoops... Please ensure to fill in all fields.'
                        ]);
                    
                    # Check the service or domain exists and the user ID matches up again, just to be sure
                    }else if ($type == 'domain' && !Database::getDomainById($id)->userid == $clientID) {
                        
                        $page->setPage('init');

                        $page->setVar('message', [
                            'type'      => 'error',
                            'content'   => 'Whoops... Either that domain does not exist, or it does not belong to you.'
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer on a domain that either does not belong to them, or does not exist (Service ID: ' . $id . ')', $clientID);

                    } else if ($type == 'service' && !Database::getServiceById($id)->userid == $clientID) {

                        $page->setPage('init');

                        $page->setVar('message', [
                            'type'      => 'error',
                            'content'   => 'Whoops... Either that service does not exist, or it does not belong to you.'
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer on a service that either does not belong to them, or does not exist (Service ID: ' . $id . ')', $clientID);

                    # Check the recipients email is actually a client
                    } else if (!\WHMCS\User\Client::where('email', $recipientEmail)->first()) {

                        $page->setPage('init');

                        $page->setMultiVars([
                            'type'      => $type,
                            'id'        => $id,
                            'message'   => [
                                'type'      => 'error',
                                'content'   => 'Whoops... No such client exists with that email address.'
                            ]
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer to a client that does not exist (Recipient Email: ' . $recipientEmail . ', Service ID: ' . $id . ')', $clientID);

                    } else if ($recipientemail == \WHMCS\User\Client::where('id', $clientID)->first()->email) {

                        $page->setPage('init');

                        $page->setMultiVars([
                            'type'      => $type,
                            'id'        => $id,
                            'message'   => [
                                'type'      => 'error',
                                'content'   => 'Whoops... You cannot transfer services/domains to yourself!'
                            ]
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer to themselves (Service ID: ' . $id . ')', $clientID);

                    } else if (TransferModel::getByRelationship($clientID, \WHMCS\User\Client::where('email', $recipientEmail)->first()->id, $type, $id, ['pending'])->count()) {
                        
                        $page->setPage('init');

                        $page->setMultiVars([
                            'type'      => $type,
                            'id'        => $id,
                            'message'   => [
                                'type'      => 'error',
                                'content'   => 'Whoops... A transfer for this service to that client is already in place.'
                            ]
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer to a client, however a trasfer for this service to the recipient client already exists (Recipient Email: ' . $recipientEmail . ', Service ID: ' . $id . ')', $clientID);

                    } else {

                        # Create transfer.
                        TransferModel::create($clientID, $recipientEmail, $type, $id);
                        
                        $page->setPage('home');

                        $days       = (Config::get('expiry_days') == 1) ? '1 day' : Config::get('expiry_days') . ' days';
                        $content    = (Config::get('expiry_days') != 0) ? 'Transfer initiated successfully. If the gaining client does not accept the transfer request within ' . $days . ', it will be cancelled.' : 'Transfer initiated successfully.';

                        $page->setVar('message', [
                            'type'      => 'success',
                            'content'   => $content
                        ]);

                        Logger::add('client', 'success', 'Client initiated transfer successfully. (Recipient Email: ' . $recipientEmail . ', Service ID: ' . $id . ')', $clientID);
                    
                    }

                } else {

                    if ($type == 'domain' && !Database::getDomainById($id)->userid == $clientID) {


                        $page->setPage('home');

                        $page->setVar('message', [
                            'type'      => 'error',
                            'content'   => 'Whoops... Either that domain does not exist, or it does not belong to you.'
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer on a domain that either does not belong to them, or does not exist (Service ID: ' . $id . ')', $clientID);

                    } else if ($type == 'service' && !Database::getServiceById($id)->userid == $clientID) {

                        $page->setPage('home');

                        $page->setVar('message', [
                            'type'      => 'error',
                            'content'   => 'Whoops... Either that service does not exist, or it does not belong to you.'
                        ]);

                        Logger::add('client', 'error', 'Client tried to initiate transfer on a service that either does not belong to them, or does not exist (Service ID: ' . $id . ')', $clientID);

                    } else {

                        $page->setPage('init');

                        $page->setMultiVars([
                            'type'  => $type,
                            'id'    => $id
                        ]);

                    }

                }

                break;

            case 'incoming':

                $page->setPage('incoming');
                break;

            case 'cancel':

                $page->setPage('home');

                $id = $page->getID();

                # Make sure currently logged in user is the owner of the transfer
                $transferDetails = Transfer::getById($id);
                
                if ($transferDetails->losing_client_id != $clientID || $transferDetails->status != "pending") {
                    
                    $page->setVar('message', [
                        'type'      => 'error',
                        'content'   => 'Whoops... You are not authorized to perform that action.'
                    ]);

                    Logger::add('client', 'error', 'Client tried to cancel a transfer however it either does not belong to them, does not exist or has already been cancelled (Transfer ID: ' . $id . ')', $clientID);
                    
                } else {

                    Transfer::cancel($id);

                    $page->setVar('message', [
                        'type'      => 'success',
                        'content'   => 'The transfer to ' . $transferDetails->gaining_client_email . ' has been cancelled.'
                    ]);

                    Logger::add('client', 'success', 'Client cancelled service transfer (Transfer ID: ' . $id . ')', $clientID);

                }

                break;

            case 'deny':

                $page->setPage('incoming');

                if ($token = $page->getToken()) {

                    $transferDetails = Transfer::getByToken($token);

                    $id = $transferDetails->id;

                } else {

                    $id = $page->getID();

                    # Make sure currently logged in user is the recipient of the transfer
                    $transferDetails = Transfer::getById($id);
                }

                if ($transferDetails->gaining_client_id != $clientID || $transferDetails->status != "pending") {
                    
                    $page->setVar('message', [
                        'type'      => 'error',
                        'content'   => 'Whoops... You are not authorized to perform that action.'
                    ]);

                    Logger::add('client', 'error', 'Client tried to deny a transfer however its either not for them, does not exist or has already been denied (Transfer ID: ' . $id . ')', $clientID);
                    
                } else {

                    Transfer::deny($id);

                    $page->setVar('message', [
                        'type'      => 'success',
                        'content'   => 'The transfer from ' . $transferDetails->losing_client_email . ' has been denied.'
                    ]);

                    Logger::add('client', 'success', 'Client denied service transfer (Transfer ID: ' . $id . ')', $clientID);

                }

                break;

            case 'accept':

                $page->setPage('incoming');

                if ($token = $page->getToken()) {

                    $transferDetails = Transfer::getByToken($token);

                    $id = $transferDetails->id;

                } else {

                    $id = $page->getID();

                    # Make sure currently logged in user is the recipient of the transfer
                    $transferDetails = Transfer::getById($id);

                }

                if ($transferDetails->gaining_client_id != $clientID || $transferDetails->status != "pending") {
                    
                    $page->setVar('message', [
                        'type'      => 'error',
                        'content'   => 'Whoops... You are not authorized to perform that action.'
                    ]);

                    Logger::add('client', 'error', 'Client tried to accept a transfer however its either not for them, does not exist or has already been accepted (Transfer ID: ' . $id . ')', $clientID);
                    
                } else {

                    Transfer::accept($id);

                    $page->setVar('message', [
                        'type'      => 'success',
                        'content'   => 'The transfer from ' . $transferDetails->losing_client_email . ' has been accepted. This service/domain is now active on your account and an email with further instructions has been sent to you.'
                    ]);

                    Logger::add('client', 'success', 'Client accepted service transfer (Transfer ID: ' . $id . ')', $clientID);

                }

                break;
            
            default:
                
                $page->setPage('home');
                break;

        }

        return $page->showPage();

    }

}