<?php

namespace Parser;

use JetBrains\PhpStorm\NoReturn;
use League\Csv\ByteSequence;
use League\Csv\Writer;

class CsvExporter
{

    private const FILENAME = "service_request.csv";
    private $parsedData;
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


    /**
     * @throws \Exception
     */
    public function __construct(string $filepath)
    {
        $htmlParser = new \Parser\HtmlParser($filepath);
        $this->parsedData = $htmlParser->getParsedData();

        $this->setContent($this->parsedData);

        try{
            $this->setupWriter();
        }catch (\League\Csv\Exception $e){
            throw new \Exception('An error occurred during writer setup. Message:'  . $e->getMessage());
        }
    }

    /**
     * Trigger csv file download
     *
     * @return void
     */
    #[NoReturn] public function getCSVFile(){
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
    private function setupWriter(): void
    {
        $this->writer = Writer::createFromString();
        $this->writer->insertOne($this->header);
        $this->writer->insertOne($this->content);
        $this->writer->setOutputBOM(ByteSequence::BOM_UTF8);
    }

    private function setContent(array $parsedData): void
    {
        foreach ($parsedData as $dataItem) {
            if (is_array($dataItem)) {
                foreach ($dataItem as $item) {
                    $this->content[] = $item;
                }
            } else {
                $this->content[] = $dataItem;
            }
        }
    }


}
