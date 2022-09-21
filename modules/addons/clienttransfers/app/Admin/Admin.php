<?php

namespace LMTech\ClientTransfers\Admin;

use LMTech\ClientTransfers\Transfer\Transfer;
use LMTech\ClientTransfers\Helpers\AdminPageHelper;

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

class Admin {

    public static function output($vars) {
      
        $passThru = [
            'moduleLink' => $vars['modulelink'],
            'alerts' => [],
            'formData' => []
        ];


        if (AdminPageHelper::getCurrentPage() == 'dashboard') {

            $passThru['transfers'] = [
                'pendingTransfers'      => Transfer::getByStatus('transfer', 'pending'),
                'completedTransfers'    => Transfer::getByStatus('transfer', 'accepted'),
                'deniedTransfers'       => Transfer::getByStatus('transfer', 'denied'),
                'cancelledTransfers'    => Transfer::getByStatus('transfer', 'cancelled'),
                'servicesTransferred'   => Transfer::getByStatus('service', 'accepted'),
                'domainsTransferred'    => Transfer::getByStatus('domain', 'accepted'),
            ];

            AdminPageHelper::outputPage($passThru);

        }

        if (AdminPageHelper::getCurrentPage() == 'settings') {

            if ($_POST['settings_submit']) {
                
                $passThru['alerts'] = [
                    'success' => [
                        'title'     => 'Settings Updated Successfully',
                        'message'   => 'Your settings have been saved.'
                    ]
                ];

            }

        }

        AdminPageHelper::outputPage($passThru);

    }

}