<?php

namespace LMTech\ClientTransfers\Database;

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
use LMTech\ClientTransfers\Email\Email;
use LMTech\ClientTransfers\Config\Config;

class Database {

    ## GENERAL 

    public static function createTables() {

        $status = false;

        try {

            Capsule::schema()->create('mod_clienttransfers_transfers', function ($table) {
                $table->increments('id');
                $table->integer('losing_client_id');
                $table->integer('gaining_client_id');
                $table->string('losing_client_name', 255);
                $table->string('losing_client_email', 255);
                $table->string('gaining_client_email', 255);
                $table->string('type', 25);
                $table->integer('service_id')->nullable();
                $table->integer('domain_id')->nullable();
                $table->datetime('requested_at');
                $table->datetime('completed_at');
                $table->string('status', 25);
                $table->text('token')->nullable();
            });
    
            $status = true;
            
        } catch (\Exception $e) {
            $status = 'Unable to create database table "mod_clienttransfers_transfers". The following error was returned: ' . $e->getMessage();
        }

        try {

            Capsule::schema()->create('mod_clienttransfers_logs', function ($table) {
                $table->increments('id');
                $table->string('scope', 25);
                $table->string('type', 25);
                $table->text('message');
                $table->integer('client_id')->nullable();
                $table->integer('transfer_id')->nullable();
                $table->datetime('created_at');
            });

        } catch (\Exception $e) {
            $status = 'Unable to create database table "mod_clienttransfers_logs". The following error was returned: ' . $e->getMessage();
        }

        try {

            Capsule::table('tblemailtemplates')->insert(Email::getTemplates());
            $status = true;
        
        } catch (\Exception $e) {
            $status = 'Unable to insert email templates into the "tblemailtemplates" table. The following error was returned: ' . $e->getMessage();
        }

        return $status;

    }

    public static function deleteTables() {

        try {

            Capsule::schema()->dropIfExists('mod_clienttransfers_transfers');
    
            $status = true;
    
        } catch (\Exception $e) {
    
            $status = 'Unable to drop database table "mod_clienttransfers_transfers". The following error was returned: ' . $e->getMessage();
    
        }

        try {

            Capsule::schema()->dropIfExists('mod_clienttransfers_logs');
    
            $status = true;
    
        } catch (\Exception $e) {
    
            $status = 'Unable to drop database table "mod_clienttransfers_logs". The following error was returned: ' . $e->getMessage();
    
        }

        try {

            Capsule::table('tblemailtemplates')->where('name', 'like', 'CT_%')->delete();

        } catch (\Exception $e) {
    
            $status = 'Unable to delete custom email templates from the "tblemailtemplates" table. The following error was returned: ' . $e->getMessage();
    
        }

        return $status;

    }

    public static function insert($data, $table = 'mod_clienttransfers_transfers') {

        return Capsule::table($table)->insert($data);

    }

    public static function update($id, array $data, $tableName = 'mod_clienttransfers_transfers') {

        return Capsule::table($tableName)->where('id', $id)->update($data);

    }

    public static function getTableById($id, $table = 'mod_clienttransfers_transfers') {
        return Capsule::table($table)->where('id', $id)->first();
    }

    public static function getTableByField($field, $value, $table = 'mod_clienttransfers_transfers') {
        return Capsule::table($table)->where($field, $value)->first();
    }

    ## SERVICES/DOMAINS

    public static function getServiceById($id) {

        return Capsule::table('tblhosting')->select('tblhosting.*', 'tblproducts.name')->where('tblhosting.id', $id)->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')->first();

    }

    public static function getServiceAddons($id) {

        return Capsule::table('tblhostingaddons')->where('hostingid', $id)->get();

    }

    public static function getDomainById($id) {

        return Capsule::table('tbldomains')->where('id', $id)->first();

    }

    public static function getClientsServicesById($id) {

        return Capsule::table('tblhosting')->select('tblhosting.*', 'tblproducts.name')->where('userid', $id)->whereIn('domainstatus', explode(',', Config::get('allowed_service_statuses')))->join('tblproducts', 'tblhosting.packageid', '=', 'tblproducts.id')->get();

    }

    public static function getClientsDomainsbyId($id) {
        
        return Capsule::table('tbldomains')->where('userid', $id)->whereIn('status', explode(',', Config::get('allowed_domain_statuses')))->get();

    }

    public static function updateServiceToNewClient($serviceID, $clientID) {

        Capsule::table('tblhosting')->where('id', $serviceID)->update([
            'userid' => $clientID
        ]);

    }

    public static function updateServiceAddonsToClient($serviceID, $clientID) {

        $getAddons = Capsule::table('tblhostingaddons')->where('hostingid', $serviceID)->update([
            'userid' => $clientID
        ]);

    }

    public static function updateDomainToNewClient($domainID, $clientID) {
        Capsule::table('tbldomains')->where('id', $domainID)->update([
            'userid' => $clientID
        ]);
    }

    ## CLIENTS

    public static function getClientById($id) {
        
        return Capsule::table('tblclients')->where('id', $id)->first();

    }

    public static function getClientByEmail($email) {
        return Capsule::table('tblclients')->where('email', $email)->first();
    }

    public static function checkClientEmail($email) {

        return Capsule::table('tblclients')->where('email', $email)->where('status', 'Active')->count();

    }


    ## TRANSFERS
    
    public static function getTransfersByClientID($clientType, $clientID, array $status) {

        return Capsule::table('mod_clienttransfers_transfers')->where($clientType, $clientID)->whereIn('status', $status)->get();

    }

    public static function getTransfersByRelationship($clientID, $recipientID, $type, $id, $status) {

        return Capsule::table('mod_clienttransfers_transfers')->where('losing_client_id', $clientID)->where('gaining_client_id', $recipientID)->where("{$type}_id", $id)->whereIn('status', $status)->count();
        
    }

    ## INVOICES

    public static function getClientInvoices($clientID, $status = 'Unpaid') {

        return Capsule::table('tblinvoices')->where('userid', $clientID)->where('status', $status)->get();

    }

    public static function getInvoiceItems($invoiceID) {

        return Capsule::table('tblinvoiceitems')->where('invoiceid', $invoiceID)->get();

    }

    public static function getInvoiceItem($itemID) {

        return Capsule::table('tblinvoiceitems')->where('id', $itemID)->first();

    }

    public static function updateInvoiceItem($itemID, $data) {
        Capsule::table('tblinvoiceitems')->where('id', $itemID)->update($data);
    }

    public static function cancelInvoice($invoiceID) {

        return Capsule::table('tblinvoices')->where('id', $invoiceID)->update([
            'status' => 'Cancelled'
        ]);

    }

    public static function updateInvoice($invoiceID, $data) {

        return Capsule::table('tblinvoices')->where('id', $invoiceID)->update($data);

    }

    public static function deleteInvoiceItem($itemID) {

        return Capsule::table('tblinvoiceitems')->where('id', $itemID)->delete();

    }

}