<?php

namespace LMTech\ClientTransfers\Admin;

use LMTech\ClientTransfers\Models\TransferModel;
use LMTech\ClientTransfers\Helpers\RedirectHelper;
use LMTech\ClientTransfers\Helpers\AdminPageHelper;
use LMTech\ClientTransfers\Helpers\PaginationHelper;

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

class Admin {

    public static function output($vars) {
      
        $passThru = [
            'moduleLink'    => $vars['modulelink'],
            'alerts'        => [],
            'formData'      => []
        ];


        if (AdminPageHelper::getCurrentPage() == 'dashboard') {

            $passThru['transfers'] = [
                'completedTransfers'        => TransferModel::where('status', 'accepted'),
                'pendingTransfers'          => TransferModel::where('status', 'pending'),
                'deniedTransfers'           => TransferModel::where('status', 'denied'),
                'cancelledTransfers'        => TransferModel::where('status', 'cancelled'),
                'servicesTransferred'       => TransferModel::where('type', 'service')->where('status', 'accepted'),
                'domainsTransferred'        => TransferModel::where('type', 'domain')->where('status', 'accepted'),
            ];

        } else if (AdminPageHelper::getCurrentPage() == 'settings') {

            if ($_POST['settings_submit']) {
                
                $passThru['alerts'] = [
                    'success' => [
                        'title'     => 'Settings Updated Successfully',
                        'message'   => 'Your settings have been saved.'
                    ]
                ];

            }

        } else if (AdminPageHelper::getCurrentPage() == 'transfers') {



            $allowedTypes   = ['completed', 'pending', 'denied', 'cancelled'];
            $allowedFilters = ['all', 'service', 'domain'];

            $type   = AdminPageHelper::getAttribute('type');
            $filter = (in_array(AdminPageHelper::getAttribute('filter'), $allowedFilters)) ? AdminPageHelper::getAttribute('filter') : 'all';

            $whereQuery[] = ['status', '=',  ($type == 'completed') ? 'accepted' : $type];

            if (!$type || !in_array($type, $allowedTypes)) {
                RedirectHelper::page('transfers', ['type' => 'completed']);
            }

            if ($filter != 'all') {
                $whereQuery[] = ['type', '=', $filter];
            }

            # Search Clients..
            $losingClient       = AdminPageHelper::getAttribute('lclient');
            $recieveingClient   = AdminPageHelper::getAttribute('rclient');

            if (is_numeric($losingClient) && $losingClient !== 0) {
                $whereQuery[] = ['losing_client_id', '=', $losingClient];
            }

            if (is_numeric($recieveingClient) && $recieveingClient !== 0) {
                $whereQuery[] = ['gaining_client_id', '=', $recieveingClient];
            }

            $transfersPagination = new PaginationHelper('p', $whereQuery, 5, \LMTech\ClientTransfers\Models\TransferModel::class);

            $passThru['transferData'] = [
                'type'                  => $type,
                'filter'                => $filter,

                'transfers'             => $transfersPagination->data(),
                'transfersLinks'        => $transfersPagination->links(),
                
                'losingClients'         => \WHMCS\User\Client::whereIn('id', TransferModel::where('status', ($type == 'completed') ? 'accepted' : $type)->pluck('losing_client_id'))->get(),
                'losingClient'          => $losingClient,
                
                'receivingClients'      => \WHMCS\User\Client::whereIn('id', TransferModel::where('status', ($type == 'completed') ? 'accepted' : $type)->pluck('gaining_client_id'))->get(), 
                'recievingClient'       => $recieveingClient,
            ];

        } else if (AdminPageHelper::getCurrentPage() == 'data-output') {

            $type = AdminPageHelper::getAttribute('type');
            
            $data = TransferModel::where('status', $type)->get();


        }

        AdminPageHelper::outputPage($passThru);
        
    }

}