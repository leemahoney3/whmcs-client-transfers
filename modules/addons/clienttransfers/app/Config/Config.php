<?php

namespace LMTech\ClientTransfers\Config;

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

use WHMCS\Module\Addon\Setting as AddonSetting;

class Config {

    public static function prePopulate() {

        return [
            'name'          => 'Client Transfers',
            'description'   => 'Easily allow clients transfer services and/or domains to other clients.',
            'version'       => '1.0.0',
            'author'        => '<a href="https://leemahoney.dev">Lee Mahoney</a>',
            'fields'        => [
                'allow_domain_transfers'    => [
                    'FriendlyName'  => 'Allow Internal Domain Transfers',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'Allow internal domain transfers between clients',
                    'Default'       => 'yes'
                ],
                'allow_service_transfers'   => [
                    'FriendlyName'  => 'Allow Internal Service Transfers',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'Allow internal service transfers between clients',
                    'Default'       => 'yes'
                ],
                'allowed_domain_statuses'   => [
                    'FriendlyName'  => 'Allowed Domain Statuses',
                    'Type'          => 'text',
                    'Size'          => 75,
                    'Description'   => 'The statuses of the domain that allow it to be transferred away. Sepearate by commas',
                    'Default'       => 'Active,Grace,Redemption'
                ],
                'allowed_service_statuses'  => [
                    'FriendlyName'  => 'Allowed Service Statuses',
                    'Type'          => 'text',
                    'Size'          => 75,
                    'Description'   => 'The statuses of the service that allow it to be transferred away. Separate by commas',
                    'Default'       => 'Active,Suspended'
                ],
                'allow_client_initiate'     => [
                    'FriendlyName'  => 'Allowed Client Initiate Transfer',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'If disabled, only an administrator can initiate a transfer from the addon dashboard',
                    'Default'       => 'yes'
                ],
                'show_pending_transfers'    => [
                    'FriendlyName'  => 'Show Pending Transfers',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'If disabled, clients will not be able to see pending transfers in their client area',
                    'Default'       => 'yes'
                ],
                'show_previous_transfers'   => [
                    'FriendlyName'  => 'Show Previous Transfers',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'If disabled, clients will not be able to see previous transfers in their client area',
                    'Default'       => 'yes'
                ],
                'expiry_days'               => [
                    'FriendlyName'  => 'Auto Expiry Days',
                    'Type'          => 'text',
                    'Size'          => 75,
                    'Descrption'    => 'How many days before a transfer is automatically cancelled. Set to 0 to disable',
                    'Default'       => 7
                ],
                'send_emails'               => [
                    'FriendlyName'  => 'Send Emails',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'If enabled, clients will receive emails regarding transfers',
                    'Default'       => 'yes'
                ],
                'enable_logs'               => [
                    'FriendlyName'  => 'Enable Logs',
                    'Type'          => 'yesno',
                    'Size'          => 25,
                    'Description'   => 'If enabled, logs will be generated for all actions taken through the module',
                    'Default'       => 'yes'
                ],

                # THE BELOW ARE NOT YET IMPLEMENTED ANYWHERE
                'send_welcome_emails' => [
                    'FriendlyName' => 'Resend Welcome Emails',
                    'Type' => 'yesno',
                    'Size' => 25,
                    'Description' => 'If enabled and the service transferred has a related welcome email, it will be re-sent to the new client'
                ],
                'generate_due_invoices' => [
                    'FriendlyName' => 'Generate Due Invoices',
                    'Type' => 'yesno',
                    'Size' => 25,
                    'Description' => 'If enabled, due invoices will be generated on the new clients account after the transfer'
                ],
                'cancel_old_invoices' => [
                    'FriendlyName' => 'Cancel Old Invoices',
                    'Type' => 'yesno',
                    'Size' => 25,
                    'Description' => 'If enabled, all invoices related to the service will be cancelled on the old clients account. Non-related items will be split to a new invoice'
                ],
                'change_service_password' => [
                    'FriendlyName' => 'Change Service Password',
                    'Type' => 'yesno',
                    'Size' => 25,
                    'Description' => 'If enabled and the service supports it, the password will be changed after the service has been transferred'
                ],
                'delete_data_on_uninstall' => [
                    'FriendlyName' => 'Delete Data On Uninstall',
                    'Type' => 'yesno',
                    'Size' => 25,
                    'Description' => 'If enabled, all data will be deleted when the module is disabled'
                ],

            ]
        ];

    }

    public static function get($setting) {

        return AddonSetting::where('module', 'clienttransfers')->where('setting', $setting)->first()->value;

    }

}