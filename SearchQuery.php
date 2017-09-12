<?php

/**
 * Created by PhpStorm.
 * User: parvbhullar
 * Date: 12/09/17
 * Time: 3:57 PM
 */
class SearchQuery
{
    public $query;
    public $first;
    public $pageSize;
    public $id;
    public $guid;
    public $groupBy;
    public function __construct($search = "", $first = 0, $pageSize = 20){
        $this->query = $search;
        $this->first = $first;
        $this->pageSize = $pageSize;
    }

    public function logQuery($userId, $source){
        if($this->query && $this->first == 0){
           //Log your query to db, cache
        }
    }

    public function toJson(){
        return json_encode($this);
    }

}