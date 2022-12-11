<?php

namespace Parser;

class HtmlParser
{
    private \DOMDocument $domDocument;
    private \DOMXPath $domXpath;

    /**
     * @param string $htmlSource
     * @throws \Exception
     */
    public function __construct(string $htmlSource)
    {
        try {
            $this->domDocument = new \DOMDocument();
            @$this->domDocument->loadHTMLFile($htmlSource);
            $this->domXpath = new \DOMXPath($this->domDocument);
        }catch (\Exception $e){
            throw new \Exception('An error occurred during HtmlParser setup. Message:'. $e->getMessage());
        }

    }

    /**
     * Return array with all parsed html data
     *
     * @return array
     * @throws \Exception
     */
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
            throw new \Exception('An error occurred during parsing html data. Message:' . $e->getMessage());
        }


        return $result;

    }

    /**
     *
     * @param string $id
     * @return \DOMNodeList|false|mixed|null
     */
    private function getDomNodeListById(string $id): mixed
    {
        try{
            $domNodeList = $this->domXpath->query("//*[@id='$id']");
        }catch (\Exception $e){
            return null;
        }

        if( $domNodeList->count() < 1 ){
            return null;
        }

        return $domNodeList;

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getTrackingNumber(): mixed
    {

       $domNodeList = $this->getDomNodeListById('wo_number');

       if(!$domNodeList || !$domNodeList->item(0)){
           throw new \Exception('Failed to parse data: wo_number');
       }

       return $domNodeList->item(0)->nodeValue;

    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getPoNumber(): mixed
    {
        $domNodeList = $this->getDomNodeListById('po_number');

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: po_number');
        }

        return $domNodeList->item(0)->nodeValue;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getDateScheduled(): string
    {
        $domNodeList = $this->getDomNodeListById('scheduled_date');

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: scheduled_dater');
        }

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

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getCustomer(): mixed
    {
        $domNodeList = $this->getDomNodeListById('location_customer');

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: location_customer');
        }

        return $domNodeList->item(0)->nodeValue;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getTrade(): mixed
    {
        $domNodeList = $this->getDomNodeListById('trade');
        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: trade');
        }

        return $domNodeList->item(0)->nodeValue;
    }

    /**
     * @return float
     * @throws \Exception
     */
    private function getNTE(): float
    {
        $domNodeList = $this->getDomNodeListById('nte');

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: nte');
        }

        $nte = $domNodeList->item(0)->nodeValue;
        $nte = preg_replace('/[^0-9.]/','',$nte);

        return floatval($nte);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    private function getStoreID(): mixed
    {
        $domNodeList = $this->getDomNodeListById('location_name'); //location name instead of store_id because of wrong id in html file

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: store_id');
        }

        return $domNodeList->item(0)->nodeValue;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getAdressData(): array
    {
        $domNodeList = $this->getDomNodeListById('location_address');

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: location_address');
        }

        return $this->parseAddress($domNodeList);
    }


    /**
     * @return float
     * @throws \Exception
     */
    private function getPhone(): float
    {
        $domNodeList = $this->getDomNodeListById('location_phone');

        if(!$domNodeList || !$domNodeList->item(0)){
            throw new \Exception('Failed to parse data: location_phone');
        }

        $phone = $domNodeList->item(0)->nodeValue;
        $phone = preg_replace('/[^0-9]/','',$phone);
        return (float)$phone;
    }


    /**
     * @param \DOMNodeList $domNodeList
     * @return array
     */
    private function parseAddress(\DOMNodeList $domNodeList): array
    {

        $childNodes = $domNodeList->item(0)->childNodes;
        $addressHtml = '';

        foreach ($childNodes as $childNode) {
            $addressHtml .= $childNode->ownerDocument->saveXML($childNode);
        }

        $splittedAddressHtmlArray = explode('<br', $addressHtml);

        if(isset($splittedAddressHtmlArray[0]) && !empty($splittedAddressHtmlArray[0])){
            $street = $this->getStreetWithNumber($splittedAddressHtmlArray[0]);
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
        $postCode = (isset($restOfAddress[2]) && !empty($restOfAddress[2])) ? $restOfAddress[2] : "n/d";
        return [
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'post_code' => substr($postCode, 0, 5)];

    }

    /**
     * @param string $splittedAddressHtml
     * @return string
     */
    private function getStreetWithNumber(string $splittedAddressHtml): string
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


