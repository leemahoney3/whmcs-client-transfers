<?php

namespace LMTech\ClientTransfers;

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

use WHMCS\Database\Capsule;
use LMTech\ClientTransfers\Database\Database;

class App {

    public static function activate() {

        if ($result = Database::createTables()) {
            return [
                'status'        => 'success',
                'descrption'    => 'Module activated successfully!'
            ];
        }

        return [
            'status'        => 'error',
            'description'   => $result
        ];

    }

    public static function deactivate() {
       
        if ($result = Database::deleteTables()) {
            return [
                'status'        => 'success',
                'descrption'    => 'Module deactivated successfully!'
            ];
        }

        return [
            'status'        => 'error',
            'description'   => $result
        ];

    }

}