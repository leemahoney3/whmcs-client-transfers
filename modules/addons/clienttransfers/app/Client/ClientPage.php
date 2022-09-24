<?php

namespace LMTech\ClientTransfers\Client;

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
use LMTech\ClientTransfers\Transfer\Transfer;
use LMTech\ClientTransfers\Models\TransferModel;
use LMTech\ClientTransfers\Helpers\PaginationHelper;

class ClientPage {

    protected $clientID;
    
    protected $id, $action, $type, $token;
    
    protected $page;
    protected $vars = [];

    public function __construct($clientID) {

        $this->clientID = $clientID;

        $this->page   = 'home';
        $this->id     = (isset($_GET['id']) && !empty($_GET['id'])) ? $_GET['id'] : 0;
        $this->action = (isset($_GET['action']) && !empty($_GET['action'])) ? $_GET['action'] : null;
        $this->type   = (isset($_GET['type']) && !empty($_GET['type'])) ? $_GET['type'] : null;
        $this->token  = (isset($_GET['token']) && !empty($_GET['token'])) ? $_GET['token'] : null;

        $this->dir = dirname(__DIR__);

        $this->vars   = [
            'allowClientInitiateTransfers'  => Config::get('allow_client_initiate'),
            'showPendingTransfers'          => Config::get('show_pending_transfers'),
            'showPreviousTransfers'         => Config::get('show_previous_transfers'),
        ];

    }

    public function getAction() {
        return $this->action;
    }

    public function getType() {
        return $this->type;
    }

    public function getID() {
        return $this->id;
    }

    public function getToken() {
        return $this->token;
    }

    public function getPost($var) {
        return (isset($_POST[$var]) && !empty($_POST[$var])) ? $_POST[$var] : null;
    }

    public function setPage($page) {
        $this->page = $page;
    }

    public function setVar($var, $value) {
        $this->vars[$var] = $value;
    }

    public function setMultiVars($vars) {

        foreach ($vars as $var => $value) {
            $this->vars[$var] = $value;
        }

    }

    public function getVar($var) {
        return $this->vars[$var];
    }

    public function showPage() {

        if ($this->page == 'init') {

            $this->setMultiVars([
                'services'  => Database::getClientsServicesById($this->clientID),
                'domains'   => Database::getClientsDomainsbyId($this->clientID),
                'type'      => $this->type,
                'id'        => $this->id
            ]);

        }

        if ($this->page == 'home') {

            $pendingTransfersPagination     = new PaginationHelper('pe', [['losing_client_id', '=', $this->clientID]], 5, \LMTech\ClientTransfers\Models\TransferModel::class, ['status', ['pending']]);
            $previousTransfersPagination    = new PaginationHelper('pr', [['losing_client_id', '=', $this->clientID]], 5, \LMTech\ClientTransfers\Models\TransferModel::class, ['status', ['accepted', 'denied', 'cancelled']]);

            $this->setMultiVars([
                'incomingRequestsCount'     => TransferModel::where(['status' => 'pending', 'gaining_client_id' => $this->clientID])->count(),
        
                'pendingTransfers'          => $pendingTransfersPagination->data(),
                'pendingTransfersLinks'     => $pendingTransfersPagination->links(),
        
                'previousTransfers'         => $previousTransfersPagination->data(),
                'previousTransfersLinks'    => $previousTransfersPagination->links(),
            ]);

        }

        if ($this->page == 'incoming') {

            $incomingRequestsPagination = new PaginationHelper('in', [['gaining_client_id', '=', $this->clientID]], 5, \LMTech\ClientTransfers\Models\TransferModel::class, ['status', ['pending']]);
            $previousRequestsPagination = new PaginationHelper('pr', [['gaining_client_id', '=', $this->clientID]], 5, \LMTech\ClientTransfers\Models\TransferModel::class, ['status', ['denied', 'accepted']]);

            $this->setMultiVars([

                'incomingRequests' => [
                    'count' => TransferModel::where(['status' => 'pending', 'gaining_client_id' => $this->clientID])->count(),
                    'data'  => $incomingRequestsPagination->data(),
                    'links' => $incomingRequestsPagination->links(),
                ],

                'previousRequests' => [
                    'count' => $previousRequestsPagination->recordCount(),
                    'data' => $previousRequestsPagination->data(),
                    'links' => $previousRequestsPagination->links(),
                ],

            ]);

        }

        $this->vars['currentPage'] = $this->page;

        return [
            'pagetitle'     => 'Transfer Services',
            'breadcrumb'    => array('index.php?m=clienttransfers'=>'Transfer Services'),
            'templatefile'  => $this->page,
            'requirelogin'  => true,
            'forcessl'      => false,
            'vars'          => $this->vars
        ];

    }

}