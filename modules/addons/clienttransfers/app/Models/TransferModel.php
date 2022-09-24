<?php

namespace LMTech\ClientTransfers\Models;

use WHMCS\Carbon;

class TransferModel extends \WHMCS\Model\AbstractModel {

    protected $table = 'mod_clienttransfers_transfers';

    public function domain() {
        return $this->hasOne(\WHMCS\Domain\Domain::class, 'id', 'domain_id');
    }

    public function service() {
        return $this->hasOne(\WHMCS\Service\Service::class, 'id', 'service_id');
    }

    public function losingClient() {
        return $this->hasOne(\WHMCS\User\Client::class, 'id', 'losing_client_id');
    }

    public function gainingClient() {
        return $this->hasOne(\WHMCS\User\Client::class, 'id', 'gaining_client_id');
    }

    public function requestedAt() {
        return Carbon::parse($this->requested_at)->format('d/m/Y H:i:s');
    }

    public function completedAt() {
        return Carbon::parse($this->completed_at)->format('d/m/Y H:i:s');
    }

    public function pendingAdminLinks() {
        return '<a href="" class="btn btn-success"><i class="fas fa-check"></i></a> <a href="" class="btn btn-danger"><i class="fas fa-ban"></i></a> <a href="" class="btn btn-default"><i class="fas fa-times"></i></a>';
    }

    public function getStatus() {

        switch ($this->status) {
            case 'pending':
                return '<span class="label label-warning">Pending</span>';
                break;
            
            case 'accepted':
                return '<span class="label label-success">Accepted</span>';
                break;

            case 'denied':
                return '<span class="label label-danger">Denied</span>';
                break;

            case 'cancelled':
                return '<span class="label label-default">Cancelled</span>';
                break;
        }

    }

    public function scopeGetByRelationship($query, $clientID, $recipientID, $type, $id, $status) {

        return $query->where('losing_client_id', $clientID)->where('gaining_client_id', $recipientID)->where("{$type}_id", $id)->whereIn('status', $status);
        
    }
    

}