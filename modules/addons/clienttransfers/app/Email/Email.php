<?php

namespace LMTech\ClientTransfers\Email;

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

class Email {

    public static function send($name, $clientID, $vars = []) {

        if (Config::get('send_emails')) {

            $result = localAPI('SendEmail', [
                'messagename' => $name,
                'id' => $clientID,
                'customvars' => base64_encode(serialize($vars))
            ]); 

            if ($result['result'] == 'success') {
                Logger::add('email', 'success', 'Email successfully sent to client (Email: ' . $name . ')', $clientID);
            } else {
                Logger::add('email', 'error', 'Email failed to send to client (Email: ' . $name . ')', $clientID);
            }

        }

        # Possibly log that email sending was aborted due to config value.
        return true;

    }

    public static function getTemplates() {

        return [
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Submitted - Losing Client',
                'subject'   => 'Service Transfer Request Submitted',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>Your service transfer request to {$gaining_client_email} for the {$service_type}: {$service_domain} has been submitted successfully.</p>
                <p>Please note that the client needs to accept the transfer request before the service is moved. Once accepted, you will receive another email to confirm.</p>
                <p><strong>Please Note</strong></p>
                <p>If you wish to cancel this transfer, you may do so from the transfer dashboard so long as the recipient has not yet accepted the transfer.</p>
                <p>Once the transfer has been accepted, you cannot retrieve the service. The recipient will need to submit a new transfer request to transfer it back to you.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Completed - Losing Client',
                'subject'   => 'Service Transfer Request Completed',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>The transfer request to {$gaining_client_email} for the {$service_type}: {$service_domain} has been completed and this service is no longer on your account.</p>
                <p><strong>Please Note</strong></p>
                <p>If you did not submit this transfer request, please contact us as soon as possible.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Cancelled - Losing Client',
                'subject'   => 'Service Transfer Request Cancelled',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>The transfer request to {$gaining_client_email} for the {$service_type}: {$service_domain} has been cancelled. Please initiate a new transfer if you wish to try again.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Denied - Losing Client',
                'subject'   => 'Service Transfer Request Denied',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>The transfer request to {$gaining_client_email} for the {$service_type}: {$service_domain} has been denied by the recipient. This transfer is no longer valid.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_New Service Transfer Request - Gaining Client',
                'subject'   => 'New Service Transfer Request',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>{$losing_client_name} ({$losing_client_email}) is looking to transfer the following {$service_type} to you: {$service_domain}</p>
                <p> </p>
                <p>If you wish to accept this transfer, please click the following link: <a href="{$accept_link}">{$accept_link}</a></p>
                <p>Alternatively, if you wish to deny this transfer, please click the following link: <a href="{$deny_link}">{$deny_link}</a></p>
                <p> </p>
                <p><strong>Please Note</strong></p>
                <p>You can also view your incoming transfers and initiate new transfers through your transfer dashboard in your client area.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Completed - Gaining Client',
                'subject'   => 'Service Transfer Request Completed',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>The transfer request from {$losing_client_name} ({$losing_client_email}) for the {$service_type}: {$service_domain} has been completed and this service is now associated with your account.</p>
                <p>You may receive service related emails shortly, if the details in any of those emails are incorrect, please let us know.</p>
                <p><strong>Please Note</strong></p>
                <p>If the service had an unpaid invoice on the previous account, this will have been re-generated on your account. Please keep an eye out for any invoice emails from us over the next couple of days to avoid interruption to the service.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Cancelled - Gaining Client',
                'subject'   => 'Service Transfer Request Cancelled',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>The transfer request from {$losing_client_name} ({$losing_client_email}) for the {$service_type}: {$service_domain} has been cancelled. Please have the owner initiate a new transfer if you wish to try again.</p>
                <p>{$signature}</p>'
            ],
            [
                'type'      => 'general',
                'name'      => 'CT_Service Transfer Request Denied - Gaining Client',
                'subject'   => 'Service Transfer Request Denied',
                'message'   => '<p>Dear {$client_first_name},</p>
                <p>You have successfully denied the transfer request from {$losing_client_name} ({$losing_client_email}) for the {$service_type}: {$service_domain}. This transfer is no longer valid.</p>
                <p>{$signature}</p>'
            ],
        ];

    }

    public static function sendWelcomeEmail($serviceData) {

        $productDetails = Database::getProductDetails($serviceData->packageid);

        if ($productDetails->welcomeemail != 0) {
            
            $result = localAPI('SendEmail', [
                'messagename'   => Database::getEmailTemplateById($productDetails->welcomeemail)->name,
                'id'            => $serviceData->id,
            ]);

            if ($result['result'] == 'error') {
                Logger::add('email', 'error', 'Welcome email failed to send to client', $serviceData->userid);
            } 

        }

    }

}