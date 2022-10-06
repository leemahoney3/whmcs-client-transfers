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
 * @version    1.0.2
 * @link       https://leemahoney.dev
 */

class RedirectHelper {

    public static function to($url) {

        header("Location: {$url}");
        exit;

    }

    public static function page($page, $args = []) {

        $url = "addonmodules.php?module=clienttransfers&page={$page}";

        if(empty($args)) {
           self::to($url); 
        }

        foreach ($args as $arg => $value) {
            $url .= "&{$arg}={$value}";   
        }

        self::to($url);

    }


}