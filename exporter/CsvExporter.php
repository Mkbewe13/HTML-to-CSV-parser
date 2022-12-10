<?php

namespace exporter;

use League\Csv\ByteSequence;
use League\Csv\Writer;

class CsvExporter
{

    private const FILENAME = "service_request.csv";
    private $parsed_data;
    private $writer;
    private $header = [
        "Tracking Number",
        "PO Number",
        "Scheduled",
        "Customer",
        "Trade",
        "NTE",
        "Store ID",
        'Street',
        "City",
        "State",
        "Postcode",
        "Phone"
    ];
    private $content = [];


    public function __construct()
    {
        $htmlParser = new \Parser\HtmlParser('wo_for_parse.html');
        $this->parsed_data = $htmlParser->getParsedData();

        $this->setContent($this->parsed_data);

        try{
            $this->setupWriter();
        }catch (\League\Csv\Exception $e){
            //@TODO handle exception
        }
    }

    /**
     * Trigger csv file download
     *
     * @return void
     */
    public function getCSVFile(){
        ob_clean();
        $this->writer->output(self::FILENAME);
        die();
    }


    /**
     * Sets up writer and fill csv file with prepared data.
     *
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    private function setupWriter(){
        $this->writer = Writer::createFromString();
        $this->writer->insertOne($this->header);
        $this->writer->insertOne($this->content);
        $this->writer->setDelimiter("\t");
        $this->writer->setNewline("\r\n");
        $this->writer->setOutputBOM(ByteSequence::BOM_UTF8);
    }

    private function setContent(array $parsed_data)
    {
        foreach ($parsed_data as $data_item) {
            if (is_array($data_item)) {
                foreach ($data_item as $item) {
                    $this->content[] = $item;
                }
            } else {
                $this->content[] = $data_item;
            }
        }
        var_dump($this->content);
    }


}
