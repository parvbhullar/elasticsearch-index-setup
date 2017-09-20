# Elasticsearch Kick Starter

Elasticsearch kick starter helps you to quickly setup elastic search indexes with configurable mapping with support of hunspell, wordnet, autocompletion and shingles.

# Requirements

  - You should have elasticsearch running.
  - Hunspell and wordnet files in elasticsearch config directory.
  - You must need php too on that server otherwise you can change elasticsearch host in ESSetupService.php

### Setup
Install the dependencies and devDependencies and run the commands.
Create an index.
```sh
$ php console.php es:create:index <index_name>
$ php console.php es:create:index idx_live
```

Create a type.
```sh
$ php console.php es:create:type <index_name> <type_name> <field_title>,<search_type>,<data_type>,<store>~is_display,<field_title>,<search_type>,<data_type>,<store_or_not> <suggest_or_not>

$ php console.php es:create:type idx_city city name,search,text,true~is_display,other,integer,true~state_id,other,integer,true true

#Create type from yml configuration
$ php console.php es:create:type:yml <index_name> <type_name> <file_path>
$ php console.php es:create:type:yml idx_live type_talks data/talks.yml
```

Index data from csv file.
```sh
$ php console.php es:index:data <index_name> <type_name> <file_path>
$ php console.php es:index:data idx_city city data/cities.csv
```


ENJOY!
