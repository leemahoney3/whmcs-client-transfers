<?php

namespace LMTech\ClientTransfers\Helpers;

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

use WHMCS\Module\Gateway;
use WHMCS\Database\Capsule;

class SubscriptionHelper {

    public static function cancel($serviceData) {
            
            $gateway = new Gateway;

            $gateway->load($serviceData->paymentmethod);

            if ($gateway->functionExists('cancelSubscription')) {
                $gateway->call('cancelSubscription', ['subscriptionID' => $serviceData->subscriptionid]);
            }

            Capsule::table('tblhosting')->where('id', $serviceData->id)->update([
                'subscriptionid' => ''
            ]);

    }

}