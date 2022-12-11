<?php

if(version_compare(phpversion(),'8.0.0','<')){
    die('Parser requires at least PHP 8.0.0');
}

require_once 'vendor/autoload.php';

if(empty($_FILES['file_to_parse'])){
    include("includes/upload_for_parse.html");
}else{
    try {
        $csvExporter = new \Parser\CsvExporter($_FILES['file_to_parse']['tmp_name']);
        $csvExporter->getCSVFile();
    }catch (Exception $e){
        echo $e->getMessage();
    }

}



?>
