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

use WHMCS\Config\Setting;
use LMTech\ClientTransfers\Helpers\TemplateHelper;

class AdminPageHelper {

    protected static $pages = [
        'Dashboard'     => 'dashboard',
        'Logs'          => 'logs',
        'Settings'      => 'settings',
        'New Transfer'  => 'newtransfer'
    ];

    public static function pageExists($page) {

        return (isset($_GET[$page]) && !empty($_GET[$page]) && in_array($page, self::getAllPages())) ? true : false;

    }

    public static function getPage($page, $args) {
        TemplateHelper::getTemplate($page, $args);
    }

    public static function getAllPages() {
        return self::$pages;
    }

    public static function getCurrentPage() {

        return (isset($_GET['page']) && !empty($_GET['page'])) ? $_GET['page'] : 'none';

    }

    public static function getCurrentPageName() {

        $name = '';

        foreach (self::getAllPages() as $title => $page) {
            if (self::getCurrentPage() == $page) {
                $name = $title;
            }
        }

        return $name;

    }

    public static function outputPage($args) {

        $args['allPages']           = self::getAllPages();
        $args['currentPage']        = self::getCurrentPage();
        $args['currentPageName']    = self::getCurrentPageName();
        $args['systemURL']          = Setting::getValue('SystemURL');

        if (in_array(self::getCurrentPage(), self::getAllPages())) {

            foreach (self::getAllPages() as $page) {

                if (self::getCurrentPage() == $page) {
                    
                    self::getPage("admin." . $page, $args);

                }
                
            }

        } else {
            header("Location: {$args['moduleLink']}&page=dashboard");
            exit;
        }

    }

    public static function getAction() {
        return (isset($_GET['action']) && !empty($_GET['action'])) ? $_GET['action'] : false;
    }

}