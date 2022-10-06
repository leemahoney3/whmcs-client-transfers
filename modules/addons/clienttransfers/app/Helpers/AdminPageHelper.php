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

use WHMCS\Config\Setting;

use LMTech\ClientTransfers\Helpers\RedirectHelper;
use LMTech\ClientTransfers\Helpers\TemplateHelper;

class AdminPageHelper {

    protected static $pages = [
        [
            'name'          => 'Dashboard',
            'slug'          => 'dashboard',
            'icon'          => '<i class="fas fa-home"></i>',
            'dropdown'      => false,
            'showInNav'     => true,
        ],

        [
            'name'          => 'Transfers',
            'slug'          => 'transfers',
            'icon'          => '<i class="fas fa-exchange-alt"></i>',
            'dropdown'      => true,
            'links'         => [
                'Completed Transfers'   => '&type=completed',
                'Pending Transfers'     => '&type=pending',
                'Denied Transfers'      => '&type=denied',
                'Cancelled Transfers'   => '&type=cancelled',
            ],
            'showInNav'     => true,
        ],

        [
            'name'          => 'Logs',
            'slug'          => 'logs',
            'icon'          => '<i class="fas fa-list"></i>',
            'dropdown'      => false,
            'showInNav'     => true,
        ],

        [
            'name'          => 'Settings',
            'slug'          => 'settings',
            'icon'          => '<i class="fas fa-cog"></i>',
            'dropdown'      => false,
            'showInNav'     => true,
        ],

        [
            'name'          => 'New Transfer',
            'slug'          => 'newtransfer',
            'icon'          => '<i class="fas fa-plus-circle"></i>',
            'dropdown'      => false,
            'showInNav'     => false,
        ],      

        [
            'name' => 'Data Output',
            'slug' => 'data-output',
            'icon' => '',
            'dropdown' => false,
            'showInNav' => false
        ]

    ];

    public static function pageExists($page) {

        return (self::getAttribute($page) && !empty(self::getAttribute($page)) && in_array(self::getCurrentPage(), array_column(self::getAllPages(), 'slug'))) ? true : false;

    }

    public static function getPage($page, $args) {
        TemplateHelper::getTemplate($page, $args);
    }

    public static function getPageInfo($page, $property) {
        foreach (self::$pages as $thePage) {
            if ($thePage['slug'] == $page) {
                return $thePage[$property];
            }

            
        }
        return null;
    }

    public static function getAllPages($nav = false) {

        $pages = [];

        if ($nav) {

            foreach (self::$pages as $page) {
                if ($page['showInNav']) {
                    $pages[] = $page;
                }
            }

        } else {
            $pages = self::$pages;
        }

        return $pages;
    }

    public static function getCurrentPage() {
        
        return (!empty(self::getAttribute('page'))) ? self::getAttribute('page') : 'none';

    }

    public static function getCurrentPageName() {

        $name = '';

        foreach (self::getAllPages() as $page) {
            if (self::getCurrentPage() == $page['slug']) {
                $name = $page['name'];
            }
        }

        return $name;

    }

    public static function outputPage($args) {

        $args['allPages']           = self::getAllPages();
        $args['allNavPages']        = self::getAllPages(true);
        $args['currentPage']        = self::getCurrentPage();
        $args['currentPageName']    = self::getCurrentPageName();
        $args['systemURL']          = Setting::getValue('SystemURL');

        

        if (in_array(self::getCurrentPage(), array_column(self::getAllPages(), 'slug'))) {
            
            foreach (self::getAllPages() as $page) {

                if (self::getCurrentPage() != $page['slug']) {
                    continue;
                }

                self::getPage("admin.{$page['slug']}", $args);
                
            }

        } else {
            RedirectHelper::page('dashboard');
        }

    }

    public static function getAction() {
        return self::getAttribute('action');
    }

    public static function getAttribute($attr) {
        return $_GET[$attr];
    }

}