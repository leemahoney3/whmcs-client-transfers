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
 * @version    1.0.2
 * @link       https://leemahoney.dev
 */

use WHMCS\Carbon;
use WHMCS\Domain\Domain;
use WHMCS\Service\Service;
use WHMCS\View\Menu\Item as MenuItem;

use LMTech\ClientTransfers\Config\Config;
use LMTech\ClientTransfers\Models\Transfer;
use LMTech\ClientTransfers\Handlers\Transfer as TransferHandler;

require_once __DIR__ . '/vendor/autoload.php';

function transfer_service_domain_sidebar(MenuItem $primarySidebar) {

    $uriParts   = explode('&amp;id=', $_SERVER['REQUEST_URI']);
    $page       = $uriParts[0];
    $id         = (isset($_GET['id']) && !empty($_GET['id'])) ? $_GET['id'] : 0;

    if ($page == '/clientarea.php?action=productdetails' || $page == '/clientarea.php?action=domaindetails') {

        // Domains
        if (Config::get('allow_domain_transfers')) {

            if (!is_null($primarySidebar->getChild('Domain Details Management'))) {

                $domain = Domain::where('id', $id);

                if (in_array($domain->status, explode(',', Config::get('allowed_domain_statuses')))) {
                    $primarySidebar->getChild('Domain Details Management')
                    ->setClass('panel-default panel-actions view-filter-btns')
                    ->addChild('Transfer Domain Internally')
                    ->setLabel('Transfer Domain Internally')
                    ->setUri('index.php?m=clienttransfers&action=init&type=domain&id=' . $domain->id)
                    ->setOrder(999);
                }

            }

        }

        // Services
        if (Config::get('allow_service_transfers')) {
            
            if (!is_null($primarySidebar->getChild('Service Details Actions'))) {

                $service = Service::where('id', $id)->first();

                if (in_array($service->domainstatus, explode(',', Config::get('allowed_service_statuses')))) {
                    $primarySidebar->getChild('Service Details Actions')
                    ->setClass('panel-default panel-actions view-filter-btns')
                    ->addChild('Transfer Service Internally')
                    ->setLabel('Transfer Service Internally')
                    ->setUri('index.php?m=clienttransfers&action=init&type=service&id=' . $service->id)
                    ->setOrder(999);
                }

            }

        }

    }

}

function transfer_services_primary_navbar(MenuItem $primaryNavbar) {

    if (!is_null($primaryNavbar->getChild('Services'))) {

        $primaryNavbar->getChild('Services')
        ->addChild('Transfer Services')
        ->setUri('index.php?m=clienttransfers')
        ->setOrder(90);

    }

}

function auto_cancel_requests($vars) {

    $transferRequests = Transfer::where('status', 'pending')->get();

    foreach ($transferRequests as $transfer) {

        $expiryDate = Carbon::parse($transfer->requested_at)->addDay(Config::get('expiry_days'));
        $todaysDate = Carbon::now();

        if ($todaysDate->gt($expiryDate)) {
            TransferHandler::cancel($transfer->id);
        }

    }

}



add_hook('ClientAreaPrimaryNavbar', 1, 'transfer_services_primary_navbar');
add_hook('ClientAreaPrimarySidebar', 1, 'transfer_service_domain_sidebar');

if (Config::get('expiry_days') != 0) {
    add_hook('AfterCronJob', 1, 'auto_cancel_requests');
}