<?php

require_once 'vendor/autoload.php';


$htmlParser = new \Parser\HtmlParser('wo_for_parse.html');
var_dump($htmlParser->getParsedData());

?>
