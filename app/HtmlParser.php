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
            $result['phone'] = $this->getPhone();
        }catch (\Exception $e){
            //@TODO handle error message
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

        $date = explode(' ', trim($domNodeList->item(0)->nodeValue));

        foreach ($date as $key=>$value){
            if (empty($value) || !preg_match('/^[a-zA-Z0-9,:\w]{3,}$/', $value)){
                unset($date[$key]);
            }
        }
        $date = array_values($date);
        $date=implode(' ',$date);


        $date = \DateTime::createFromFormat('F d, Y g:i',$date);

        return $date ? $date->format('Y-m-d H:i') : 'n/d';
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

        $nte = $domNodeList->item(0)->nodeValue;
        $nte = preg_replace('/[^0-9.]/','',$nte);

        return floatval($nte);
    }

    private function getStoreID()
    {
        $domNodeList = $this->getDomNodeListById('location_name'); //location name instead of store_id because of wrong id in html file

        return $domNodeList->item(0)->nodeValue;
    }

    private function getAdressData(): array
    {
        $domNodeList = $this->getDomNodeListById('location_address');

        return $this->parseAddress($domNodeList);
    }


    private function getPhone(): float
    {
        $domNodeList = $this->getDomNodeListById('location_phone');
        $phone = $domNodeList->item(0)->nodeValue;
        $phone = preg_replace('/[^0-9]/','',$phone);
        return (float)$phone;
    }


    private function parseAddress(\DOMNodeList $domNodeList): array
    {

        $childNodes = $domNodeList->item(0)->childNodes;
        $addressHtml = '';

        foreach ($childNodes as $childNode) {
            $addressHtml .= $childNode->ownerDocument->saveXML($childNode);
        }

        $splittedAddressHtmlArray = explode('<br', $addressHtml);

        if(isset($splittedAddressHtmlArray[0]) && !empty($splittedAddressHtmlArray[0])){
            $street = $this->get_street_with_number($splittedAddressHtmlArray[0]);
        }else{
            $street = 'n/d';
        }

        $restOfAddress = $splittedAddressHtmlArray[1];
        $restOfAddress = explode(' ', $restOfAddress);

        foreach ($restOfAddress as $key => $value) {
            if ((empty($value) || !preg_match('/^[a-zA-Z0-9\w]*$/', $value)) && !preg_match('/\d{5}/', $value)) {
                unset($restOfAddress[$key]);
            }
        }
        $restOfAddress = array_values($restOfAddress);
        $city = (isset($restOfAddress[0]) && !empty($restOfAddress[0])) ? $restOfAddress[0] : "n/d";
        $state = (isset($restOfAddress[1]) && !empty($restOfAddress[1])) ? $restOfAddress[1] : "n/d";
        $post_code = (isset($restOfAddress[2]) && !empty($restOfAddress[2])) ? $restOfAddress[2] : "n/d";
        return [
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'post_code' => substr($post_code, 0, 5)];

    }

    private function get_street_with_number(string $splittedAddressHtml): string
    {

        $street = explode(' ', strip_tags($splittedAddressHtml));

        foreach ($street as $key => $value) {
            if (empty($value) || !preg_match('/^[a-zA-Z0-9\w]*$/', $value)) {
                unset($street[$key]);
            }
        }

        return implode(' ', $street);
    }



}


