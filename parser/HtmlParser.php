<?php

namespace parser;

class HtmlParser
{
    private \DOMDocument $dom_document;
    private \DOMXPath $dom_xpath;


    public function __construct(string $html_source)
    {
        try {
            $this->dom_document = new \DOMDocument();
            @$this->dom_document->loadHTMLFile($html_source);
            $this->dom_xpath = new \DOMXPath($this->dom_document);
        }catch (\Exception $e){
            //@TODO handle exception
        }

    }


    public function getParsedData(): array{
        $result = array();

        try {
            $result['tracking_number'] = $this->getTrackingNumber();
            $result['po_number'] = $this->getPoNumber();
            $result['data_scheduled'] = $this->getDateScheduled();
            $result['customer'] = $this->getCustomer();
            $result['trade'] = $this->getTrade();
            $result['nte'] = $this->getNTE();
            $result['store_id'] = $this->getStoreID();
            $result['address'] = $this->getAdressData();
        }catch (\Exception $e){
            //@TODO obsłuzyc error message
        }


        return $result;

    }

    private function getDomNodeListById(string $id){
        try{
            $domNodeList = $this->dom_xpath->query("//*[@id='$id']");
        }catch (\Exception $e){
            return null;
        }

        if( $domNodeList->count() < 1 ){
            return null;
        }

        return $domNodeList;

    }

    private function getTrackingNumber()
    {

       $domNodeList = $this->getDomNodeListById('wo_number');

       return $domNodeList->item(0)->nodeValue;

    }

    private function getPoNumber()
    {
        $domNodeList = $this->getDomNodeListById('po_number');

        return $domNodeList->item(0)->nodeValue;
    }

    private function getDateScheduled()
    {
        $domNodeList = $this->getDomNodeListById('scheduled_date');


        return $domNodeList->item(0)->nodeValue;
    }

    private function getCustomer()
    {
        $domNodeList = $this->getDomNodeListById('location_customer');


        return $domNodeList->item(0)->nodeValue;
    }

    private function getTrade()
    {
        $domNodeList = $this->getDomNodeListById('trade');


        return $domNodeList->item(0)->nodeValue;
    }

    private function getNTE()
    {
        $domNodeList = $this->getDomNodeListById('nte');


        return $domNodeList->item(0)->nodeValue;
    }

    private function getStoreID()
    {
        $domNodeList = $this->getDomNodeListById('store_id');

        return $domNodeList->item(0)->nodeValue;
    }

    private function getAdressData()
    {
        $domNodeList = $this->getDomNodeListById('location_address');


        return $this->parseAdress($domNodeList->item(0)->nodeValue);
    }

    private function parseAdress(string $fullAdress): array{





    }

}