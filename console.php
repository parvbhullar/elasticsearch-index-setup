#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: parvbhullar
 * Date: 07/09/17
 * Time: 5:36 PM
 */

//php core/console.php command
//php core/console.php dump_assets
//$argv[1]
umask(0000);
set_time_limit(0);
#Require
require dirname(__FILE__).  '/vendor/autoload.php';
require dirname(__FILE__).  '/ESSetupService.php';


class Console
{
    private $argv = array();
    public function __construct($argv)
    {
        $command = isset($argv[1]) ? $argv[1] : "Undefined";
        switch ($command) {
            case "es:create:index":
                $index = isset($argv[2])? $argv[2] : "Undefined";
                $this->esCreateIndex($index);
                break;
            case "es:create:type":
                $index = isset($argv[2])? $argv[2] : "Undefined";
                $type = isset($argv[3])? $argv[3] : "Undefined";
                $fields = isset($argv[4])? $argv[4] : "Undefined";
//                echo $fields;exit;
                $suggest = isset($argv[5])? $argv[5] : false;
                $this->esCreateDocType($index, $type, $fields, $suggest);
                break;
        }
    }

    public function esCreateIndex($indexName){
        $esService = new ESSetupService($indexName);
        $esService->createIndex();
    }

    public function esCreateDocType($index, $type, $fields, $suggest){
        $esService = new ESSetupService($index);
        $esService->createDocTypeFromFieldsString($type, $fields, $suggest);
    }
}

$app = new Console($argv);

