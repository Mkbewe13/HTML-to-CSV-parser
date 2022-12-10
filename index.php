<?php

require_once 'vendor/autoload.php';

include('exporter/CsvExporter.php');
$htmlParser = new \Parser\HtmlParser('wo_for_parse.html');;
$csv_exporter = new \exporter\CsvExporter();
$csv_exporter->getCSVFile();
?>
