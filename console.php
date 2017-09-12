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
require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/ESSetupService.php';


class Console
{
    private $argv = array();

    public function __construct($argv)
    {
        $command = isset($argv[1]) ? $argv[1] : "Undefined";
        switch ($command) {
            case "es:create:index":
                $index = isset($argv[2]) ? $argv[2] : "Undefined";
                $this->esCreateIndex($index);
                break;
            case "es:create:type":
                $index = isset($argv[2]) ? $argv[2] : "Undefined";
                $type = isset($argv[3]) ? $argv[3] : "Undefined";
                $fields = isset($argv[4]) ? $argv[4] : "Undefined";
//                echo $fields;exit;
                $suggest = isset($argv[5]) ? $argv[5] : false;
                $this->esCreateDocType($index, $type, $fields, $suggest);
                break;
            case "es:index:data":
                $index = isset($argv[2]) ? $argv[2] : "Undefined";
                $type = isset($argv[3]) ? $argv[3] : "Undefined";
                $fileName = isset($argv[4]) ? $argv[4] : "Undefined";
                $this->esIndexData($index, $type, $fileName);
                break;
        }
    }

    public function esCreateIndex($indexName)
    {
        $esService = new ESSetupService($indexName);
        $esService->createIndex();
    }

    public function esCreateDocType($index, $type, $fields, $suggest)
    {
        $esService = new ESSetupService($index);
        $esService->createDocTypeFromFieldsString($type, $fields, $suggest);
    }

    public function esIndexData($index, $type, $fileName)
    {
        $esService = new ESSetupService($index);
        $data = $this->readCSV($fileName);
        $esService->pushData($type, $data);
    }

    function readCSV($csvFile)
    {
        $file_handle = fopen($csvFile, 'r');
        $line_of_text = [];
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 1024);
        }
        fclose($file_handle);

        $data = [];
        if (count($line_of_text) > 0) {
            $columns = $line_of_text[0];
            foreach ($line_of_text as $i => $v) {
                if ($i > 0) {
                    $obj = [];
                    foreach ($columns as $j => $c)
                        $obj[$c] = $v[$j];
                    $data[] = $obj;
                }
            }
        }
        return $data;
    }
}

$app = new Console($argv);

