<?php

namespace LMTech\ClientTransfers\Logger;

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
use LMTech\ClientTransfers\Database\Database;

class Logger {

    public static function add($scope, $type, $message, $clientID = null, $transferID = null) {

        if (Config::get('enable_logs')) {
            Database::insert([
                'scope'         => $scope,
                'type'          => $type,
                'message'       => $message,
                'client_id'     => $clientID,
                'transfer_id'   => $transferID,
                'created_at'    => date('Y-m-d H:i:s')
            ], 'mod_clienttransfers_logs');
        }
    
    }

}