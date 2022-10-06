<?php

namespace LMTech\ClientTransfers\Models;

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

use LMTech\ClientTransfers\Config\Config;

class Log extends \WHMCS\Model\AbstractModel {

    protected $table = 'mod_clienttransfers_logs';

    public static function add($scope, $type, $message, $clientID = null, $transferID = null) {

        if (Config::get('enable_logs')) {
            self::insert([
                'scope'         => $scope,
                'type'          => $type,
                'message'       => $message,
                'client_id'     => $clientID,
                'transfer_id'   => $transferID,
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        }
    
    }

}