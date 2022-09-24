<?php

namespace LMTech\ClientTransfers\Helpers;

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