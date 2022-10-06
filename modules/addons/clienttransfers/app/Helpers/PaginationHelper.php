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

class PaginationHelper {

    private $pageName;
    private $limit;
    private $model;

    private $where;
    private $whereInArray;

    private $recordCount;
    private $pages;

    private $page;

    private $offset;

    public function __construct($pageName = 'page', $where = [], $limit = 10, $model, $whereInArray = []) {

        $this->pageName     = $pageName;
        $this->where        = $where;
        $this->limit        = $limit;
        $this->model        = $model;
        
        $this->whereInArray = $whereInArray;

        $this->recordCount  = $this->getRecordCount();
        $this->pages        = $this->recordCount / $this->limit;

        $this->page         = (int) (AdminPageHelper::getAttribute($this->pageName) != null) ? AdminPageHelper::getAttribute($this->pageName) : 1;

        $this->offset       = ($this->page - 1) * $this->limit;

    }

    # Output data based on properties
    public function data() {

        if (empty($this->where) && empty($this->whereInArray)) {

            $result = $this->model::offset($this->offset)->limit($this->limit);
        
        } else if (!empty($this->where) && empty($this->whereInArray)) {
        
            $result = $this->model::where($this->where)->offset($this->offset)->limit($this->limit);
        
        } else if (empty($this->where) && !empty($this->whereInArray)) {
        
            $result = $this->model::where($this->where)->whereIn($this->whereInArray[0], $this->whereInArray[1])->offset($this->offset)->limit($this->limit);
            
        } else {
        
            $result = $this->model::where($this->where)->whereIn($this->whereInArray[0], $this->whereInArray[1])->offset($this->offset)->limit($this->limit);
        
        }

        return $result->get()->sortByDesc('requested_at');

    }

    # Output links (using Bootstrap styling) for the relevant data above
    public function links() {

        $html = '';

        if ($this->recordCount < ($this->limit + 1)) {
            $html .= '<div class="clearfix">';
            $html .= '<div class="text-sm-left float-left pull-left">Showing <b>' . $this->recordCount . '</b> out of <b>' . $this->recordCount . '</b> records</div>';
            $html .= '</div>'; 
        }

        if ($this->recordCount > $this->limit) {
            $html .= '<div class="clearfix">';

            //if (($this->page * $this->limit) < $this->recordCount) {
                $html .= '<div class="text-sm-left float-left pull-left">Showing <b>' . $this->offset . '</b> to <b>' . ($this->page * $this->limit) . '</b> out of <b>' . $this->recordCount . '</b> records</div>';
            //} else {
              //  $html .= '<div class="text-sm-left float-left pull-left">Showing <b>' . $this->recordCount . '</b> out of <b>' . $this->recordCount . '</b> records</div>';
            //}

            $html .= '<ul style="margin: 0px 0px" class="pagination float-right pull-right">';
            
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

    public function recordCount() {
        return $this->recordCount;
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

    private function getRecordCount() {

        if (empty($this->where) && empty($this->whereInArray)) {

            $result = $this->model::offset($this->offset)->limit($this->limit);
        
        } else if (!empty($this->where) && empty($this->whereInArray)) {
        
            $result = $this->model::where($this->where)->offset($this->offset)->limit($this->limit);
        
        } else if (empty($this->where) && !empty($this->whereInArray)) {
        
            $result = $this->model::where($this->where)->whereIn($this->whereInArray[0], $this->whereInArray[1])->offset($this->offset)->limit($this->limit);
            
        } else {
        
            $result = $this->model::where($this->where)->whereIn($this->whereInArray[0], $this->whereInArray[1])->offset($this->offset)->limit($this->limit);
        
        }

        return $result->count();

    }


}