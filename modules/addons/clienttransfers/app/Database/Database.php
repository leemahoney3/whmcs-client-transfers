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
 * @version    1.0.2
 * @link       https://leemahoney.dev
 */

use WHMCS\Database\Capsule;
use LMTech\ClientTransfers\Handlers\Email;

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
                $table->datetime('updated_at');
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
                $table->datetime('updated_at');
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

}