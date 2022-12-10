<?php

require_once 'vendor/autoload.php';

if(empty($_FILES['file_to_parse'])){
    include("includes/upload_for_parse.html");
}else{
    try {
        $csvExporter = new \Parser\CsvExporter($_FILES['file_to_parse']['tmp_name']);
        $csvExporter->getCSVFile();
    }catch (Exception $e){
        echo 'Something went wrong while downloading the csv file. Message ' . $e->getMessage();
    }

}



?>
