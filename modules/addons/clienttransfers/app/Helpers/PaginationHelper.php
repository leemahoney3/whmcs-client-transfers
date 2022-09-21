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

use WHMCS\Database\Capsule;

class PaginationHelper {

    private $pageName;
    private $limit;

    private $table;
    private $whereArray;
    private $whereInArray;

    private $recordCount;
    private $pages;

    private $page;

    private $offset;

    public function __construct($pageName = 'page', $whereArray = [], $whereInArray = [], $limit = 2, $table = 'mod_clienttransfers_transfers') {

        $this->pageName     = $pageName;
        $this->table        = $table;
        $this->whereArray   = $whereArray;
        $this->whereInArray = $whereInArray;
        $this->offset       = $offset;
        $this->limit        = $limit;
        $this->recordCount  = Capsule::table($table)->where($whereArray)->whereIn($whereInArray['column'], $whereInArray['data'])->count();
        $this->pages        = $this->recordCount / $limit;

        $this->page         = (int) isset($_GET[$pageName]) ? $_GET[$pageName] : 1;

        $this->offset       = ($this->page - 1) * $this->limit;
    }

    # Output data based on properties
    public function data() {

        if (is_null($this->recordCount)) {
            return;
        }
        
        return Capsule::table('mod_clienttransfers_transfers')->where($this->whereArray)->whereIn($this->whereInArray['column'], $this->whereInArray['data'])->offset($this->offset)->limit($this->limit)->get();

    }

    # Output links (using Bootstrap styling) for the relevant data above
    public function links() {

        $html = '';

        if ($this->recordCount < ($this->limit + 1)) {
            $html .= '<div class="clearfix">';
            $html .= '<div class="text-sm-left float-left">Showing <b>' . $this->recordCount . '</b> out of <b>' . $this->recordCount . '</b> records</div>';
            $html .= '</div>'; 
        }

        if ($this->recordCount > $this->limit) {
            $html .= '<div class="clearfix">';

            if (($this->page * $this->limit) < $this->recordCount) {
                $html .= '<div class="text-sm-left float-left">Showing <b>' . ($this->page * $this->limit) . '</b> out of <b>' . $this->recordCount . '</b> records</div>';
            } else {
                $html .= '<div class="text-sm-left float-left">Showing <b>' . $this->recordCount . '</b> out of <b>' . $this->recordCount . '</b> records</div>';
            }

            $html .= '<ul style="margin: 0px 0px" class="pagination float-right">';
            
            if ($this->page < 2) {
                $html .= '<li class="page-item disabled"><a href="#" class="page-link">Previous</a></li>';
            } else {
                $html .= '<li class="page-item"><a href="' . $this->parseURL($this->pageName, ($this->page - 1)) . '" class="page-link">Previous</a></li>';
            }

            if ($this->page - 2 > 0) {
                $html .= '<li class="page-item"><a href="' . $this->parseURL($this->pageName, ($this->page - 2)) . '" class="page-link">' . ($this->page - 2) . '</a></li>';
            }

            if ($this->page - 1 > 0) {
                $html .= '<li class="page-item"><a href="' . $this->parseURL($this->pageName, ($this->page - 1)) . '" class="page-link">' . ($this->page - 1) . '</a></li>';
            }

            $html .= '<li class="page-item active"><a href="' . $this->parseURL($this->pageName, $this->page) . '" class="page-link">' . $this->page . '</a></li>';

            if (($this->page + 1) < ($this->pages + 1)) {
                $html .= '<li class="page-item"><a href="' . $this->parseURL($this->pageName, ($this->page + 1)) . '" class="page-link">' . ($this->page + 1) . '</a></li>';
            }

            if (($this->page + 2) < ($this->pages + 1)) {
                $html .= '<li class="page-item"><a href="' . $this->parseURL($this->pageName, ($this->page + 2)) . '" class="page-link">' . ($this->page + 2) . '</a></li>';
            }

            if (($this->page + 1) < ($this->pages + 1)) {
                $html .= '<li class="page-item"><a href="' . $this->parseURL($this->pageName, ($this->page + 1)) . '" class="page-link">Next</a></li>';
            } else {
                $html .= '<li class="page-item disabled"><a href="#" class="page-link">Next</a></li>';
            }

            $html .= '</ul>';
            $html .= '</div>';

        }

        return $html;


    }

    private function parseURL($parameter, $value) { 
        
        $params = []; 
        $output = '?';

        $firstRun = true; 
        
        foreach($_GET as $key => $val) { 
            
            if($key != $parameter) { 
                
                if(!$firstRun) { 
                    $output .= '&'; 
                } else { 
                    $firstRun = false; 
                }

                $output .= $key . '=' . urlencode($val); 
             }

        } 
    
        if(!$firstRun) 
            $output .= '&'; 
        
        $output .= $parameter . '=' . urlencode($value); 
        return htmlentities($output); 
    
    }


}