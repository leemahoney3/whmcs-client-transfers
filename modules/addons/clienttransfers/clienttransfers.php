<?php

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

use LMTech\ClientTransfers\App;
use LMTech\ClientTransfers\Admin\Admin;
use LMTech\ClientTransfers\Client\Client;
use LMTech\ClientTransfers\Config\Config;

require __DIR__ . '/vendor/autoload.php';

function clienttransfers_config() {

    return Config::prePopulate();

}

function clienttransfers_activate() {

    return App::activate();

}

function clienttransfers_deactivate() {

    return App::deactivate();

}

function clienttransfers_output($vars) {

    return Admin::output($vars);

}

function clienttransfers_clientarea($vars) {

    return Client::output($vars);

}