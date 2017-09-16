<?php

/**
 * Created by PhpStorm.
 * User: parvbhullar
 * Date: 07/09/17
 * Time: 5:35 PM
 */
class ESSetupService
{
    private $index, $indexName, $esClient, $tags = [], $textProcess;
    private $host = "localhost";

    public function __construct($indexName = "idx_new")
    {
        $this->indexName = $indexName;
    }

    public function getClient($host = "localhost", $port = "9200")
    {
        if (!$this->esClient) {
            $this->esClient = new \Elastica\Client(array('connections' => array(array('host' => $host, 'port' => $port))));
        }
        return $this->esClient;
    }

    public function createIndex($indexName = false)
    {
        $indexName = $indexName ? $indexName : $this->indexName;
        $elasticaClient = $this->getClient($this->host);
        $elasticaIndex = $elasticaClient->getIndex($indexName);

        $elasticaIndex->create(
            array(
                'number_of_shards' => 5,
                'number_of_replicas' => 1,
                'analysis' => array(
                    'analyzer' => array(
                        'indexAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'char_filter' => ["html_strip"],
                            'filter' => array('lowercase', 'en_US', 'filter_stop', 'my_shingle', 'synonym')
                        ),
                        'searchAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'char_filter' => ["html_strip"],
                            'filter' => array('lowercase', 'en_US', 'filter_stop', 'my_shingle')
                        ),
                        'simpleAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('lowercase', 'en_US', 'filter_stop', 'my_shingle')
                        ),
                        'autocompleteAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => array('lowercase', 'autocomplete_filter')
                        ),
                        'urlAnalyzer' => array(
                            'type' => 'custom',
                            'tokenizer' => 'letter',
                            'filter' => array('trim', 'lowercase', 'filter_url_stop')
                        )
//                    ,
//                        'myMetafone' => array(
//                            'type' => 'custom',
//                            'tokenizer' => 'standard',
//                            'filter' => array('trim', 'my_metaphone')
//                        )
                    ),
                    'tokenizer' => array(
                        'nGram' => array(
                            'type' => "nGram",
                            'min_gram' => 2,
                            'max_gram' => 50
                        )
                    ),
                    'filter' => array(
                        'en_US' => array(
                            'type' => 'hunspell',
                            'language' => 'en_US'
                        ),
                        'mySnowball' => array(
                            'type' => 'snowball',
                            'language' => 'English',
                            "stopwords_path" => "stopwords/english.txt"
                        ),
                        'synonym' => array(
                            'type' => 'synonym',
                            'format' => 'wordnet',
                            'synonyms_path' => 'analysis/wn_s.pl'
                        ),
                        'my_shingle' => array(
                            'type' => 'shingle',
                            'max_shingle_size' => '5',
                            'min_shingle_size' => '2',
                            'output_unigrams' => true
                        ),
                        'filter_stop' => array(
                            'type' => 'stop',
                            "stopwords_path" => "stopwords/english.txt"
                        ),
                        'filter_url_stop' => array(
                            'type' => 'stop',
                            "stopwords" => ["http", "https", "ftp", "www"]
                        ),
                        "autocomplete_filter" => array(
                            "type" => "edge_ngram",
                            "min_gram" => 1,
                            "max_gram" => 20
                        )
//                    ,
//                        'my_metaphone' => array(
//                            'type' => 'phonetic',
//                            'encoder' => 'double_metaphone',
//                            "replace" => false
//                        )
                    )
                ),
                "similarity" => array(
                    "my_bm25" => array(
                        "type" => "BM25",
                        "b" => 0,
                        "k1" => 0.9
                    )
                )
            ),
            true
        );
        return $elasticaIndex;
    }

    public function deleteIndex($indexName = false)
    {
        //TODO create backup snapsot before deleting index
        $client = $this->getClient();
        if ($indexName) {
            $index = $this->getIndex($indexName);
        } else {
            $index = $this->getIndex();
        }
        $status = new \Elastica\Status($client);
        // Deleting index should also remove alias
        return $index->delete();
    }

    public function getIndex($indexName = false)
    {
        $indexName = $indexName ? $indexName : $this->indexName;// = $indexName;
        $elasticaClient = $this->getClient($this->host);
        $elasticaIndex = $elasticaClient->getIndex($indexName);
        return $elasticaIndex;
    }

    public function addMappingField($type = "na", $name = 'un', $dataType = "text", $store = false)
    {
        $store = $store ? "yes" : "no";
        switch ($type) {
            case "search":
                return array(
                    'type' => $dataType,
                    'store' => $store,
                    'analyzer' => 'indexAnalyzer',
                    'search_analyzer' => 'searchAnalyzer',
                    'fields' => array(
                        $name . '_simple' => array('type' => $dataType, 'store' => $store, 'analyzer' => 'simpleAnalyzer', 'search_analyzer' => 'simpleAnalyzer')
                    ),
                    "similarity" => "BM25"
                );
            case "url":
                return array('type' => $dataType, 'store' => $store, 'analyzer' => 'urlAnalyzer', 'search_analyzer' => 'urlAnalyzer');
            case "datetime":
                return array('type' => 'date', 'format' => 'YYYY-MM-dd HH:mm:ss', 'store' => $store);
            case "date":
                return array('type' => 'date', 'format' => 'yyyy-MM-dd', 'store' => $store);
            case "suggest":
                return array('type' => "completion", 'analyzer' => 'simpleAnalyzer');
            case "autocomplete":
                return array('type' => $dataType, 'store' => $store, 'analyzer' => 'autocompleteAnalyzer');
            case "nested":
                return array('type' => "nested", 'properties' => []);
            default :
                return array('type' => $dataType, 'store' => $store, 'index' => 'not_analyzed');
        }
    }

    public function createCustomMapping($index)
    {
        try {
            $res = $index->setSettings(
                array(
                    "settings" => array(
                        "similarity" => array(
                            "my_bm25" => array(
                                "type" => "BM25",
                                "b" => 0,
                                "k1" => 0.9
                            )
                        ))
                )
            );
        } catch (\Exception $ex) {
            print_r($ex->getMessage() . "\n");
            throw $ex;
        }
        return $index;
    }

    public function createDocType($typeName = "type_new", $fields = array("field_name" => array(
        "type" => "search", "data_type" => "text", "store" => true
    )), $suggest = false)
    {
        $index = $this->getIndex();
        $type = $index->getType($typeName);

        $mapping_array = $this->getMappingFields($fields, $suggest);
        $mapping = new \Elastica\Type\Mapping($type, $mapping_array);
        return $type->setMapping($mapping);
    }

    public function getMappingFields($fields, $suggest)
    {
        $mapping_array = array();
        foreach ($fields as $key => $field) {
            $t = isset($field['type']) ? $field['type'] : 'na';
            $data_type = isset($field['data_type']) ? $field['data_type'] : 'text';
            $store = isset($field['store']) ? $field['store'] == 1 : false;
            $f = $this->addMappingField(trim($t), $key, $data_type, $store);
            if ($t == "nested") {
                $props = isset($field['properties']) ? $field['properties'] : [];
                $props = $this->getMappingFields($props, $suggest);
                $f['properties'] = $props;
            }
            $mapping_array[$key] = $f;
        }
        if ($suggest) {
            $mapping_array["suggest"] = $this->addMappingField('suggest', 'suggest');
            $mapping_array["autocomplete"] = $this->addMappingField('autocomplete', 'text');
        }
        return $mapping_array;
    }

    public function createDocTypeFromFieldsString($typeName, $fields = "title,search,text,true;", $suggest = false)
    {
        $fields = explode("~", $fields);
        $es_fields = array();
        foreach ($fields as $field) {
            $field = explode(",", $field);
            $es_field = array(
                "type" => isset($field[1]) ? $field[1] : 'search',
                "data_type" => isset($field[2]) ? $field[2] : "text",
                "store" => isset($field[3]) ? $field[3] == 'true' : false
            );
            $es_fields[isset($field[0]) ? $field[0] : 'title'] = $es_field;
        }
//        print_r($es_fields); exit;
        $this->createDocType($typeName, $es_fields, $suggest);
    }

    public function createDocTypeFromYml($typeName, $file)
    {
        $type = spyc_load_file($file);
        $fields = $type["fields"];
        $suggest = $type["suggest"];
        $typeName = $typeName ? $typeName : $type["type"];
        $this->createDocType($typeName, $fields, $suggest);
    }

    public function getValue($array, $key, $default = ''){
        return isset($array[$key]) ? $array[$key] : $default;
    }

    public function createDoc($obj)
    {
        $doc = new \Elastica\Document($obj["id"]);
        foreach ($obj as $key => $value) {
            $doc->set($key, $this->isNull($value));
            if ($key == "name" || $key == "title" || $key == "query") {
                $doc->set("suggest", $this->isNull($value));
                $doc->set("autocomplete", $this->isNull($value));
            }
        }
        return $doc;
    }

    public function isNull($obj)
    {
        return $obj ? $obj : "";
    }

    public function pushData($typeName = "kn_docs", $data)
    {
        $index = $this->getIndex();
        $type = $index->getType($typeName);

        $documents = array();
        $response = array();
        $i = 1;
        foreach ($data as $s) {
            if (isset($s["id"])) {
                $doc = $this->createDoc($s);
                $documents[] = $doc;
                $response[] = $s["id"];
            } else {
                $response[] = "ID not exists, skipping!";
            }
        }

        if (count($documents)) {
            $type->addDocuments($documents);
            $type->getIndex()->refresh();
        }
        return $response;
    }
}