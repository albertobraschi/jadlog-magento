<?php

class Koan_Jadlog_Model_Carrier_Jadlog extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {

    /**
     * Identificador interno do módulo
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'koan_jadlog';
	protected $_cubagem  = null;

    /**
     * Verifica os pré-requisitos para funcionamento do módulo,
     * realiza o cálculo de frete e adiciona no checkout do magento
     *
     * @param Mage_Shipping_Model_Rate_Request $request Informações do pedido
     *
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        // pré-condições

        if (!$this->hasPreConditionsPassed($request)) {
            return false;
        }

        $methods = $this->getMethods();
        $result = Mage::getModel('shipping/rate_result');

        $this->_generateCubagem();

        foreach ($methods as $rMethod) {

            $method = Mage::getModel('shipping/rate_result_method');
            $api = Mage::getModel('koan_jadlog/carrier_jadlog_api');
            $origin = Mage::getStoreConfig('shipping/origin/postcode');
            $cnpjContrante = Mage::getStoreConfig('carriers/' . $this->_code . '/cnpj_contrante');
            $senhaContrante = Mage::getStoreConfig('carriers/' . $this->_code . '/senha_contrante');
            $valorColeta = Mage::getStoreConfig('carriers/' . $this->_code . '/valor_coleta');
            $freteApagar = Mage::getStoreConfig('carriers/' . $this->_code . '/frete_apagar');
            $entrega = Mage::getStoreConfig('carriers/' . $this->_code . '/entrega');
            $seguro = Mage::getStoreConfig('carriers/' . $this->_code . '/seguro');


            $cubagem =  $this->_cubagem/$api->getModal($rMethod);
            /** TEMPORARIO * */
            #$cubagem = 13.23;

            #Mage::log($this->_cubagem,null,"cubagem.log");


            $frete = $api
                    ->setContrato($cnpjContrante)
                    ->setSenha($senhaContrante)
                    ->setCepOrigem($origin)
                    ->setCepDestino($request->getDestPostcode())
                    #->setPeso($cubagem)
               		->setPeso($request->getPackageWeight())
                    ->setSeguro($seguro)
                    ->setValorDeclarado($request->getPackageValue())
                    ->setValorColeta($valorColeta)
                    ->setFreteApagar($freteApagar)
                    ->setEntrega($entrega)
                    ->setServico($rMethod);

            // Calcula o frete
            $frete = $api->dados();

            #print_r($frete);
            // erros
            # OBS REVER PARA MENSAGEM ERRO DA JADLOG
            if ($frete['erro'] != 0) {
                Mage::log('' . $frete['msgErro'] . '', null, "jadlog.log");
            }

            $frete['valor'] = $this->addTaxes($frete['valor']);

            //var_dump($frete['valor']);
            // skip if tax is zero
            if ($frete['valor'] <= 0) {
                continue;
            }

            $method->setCarrier($this->_code);
            $method->setCarrierTitle(Mage::getStoreConfig('carriers/' . $this->_code . '/title'));

            $method->setMethod(strtolower($api->getNomeServico($rMethod)));
            $title = $api->getNomeServico($rMethod);


            $method->setMethodTitle($title);
            $method->setPrice($frete['valor']);
            $result->append($method);
        }


        return $result;
    }

    /**
     * Retorna o nome do serviço de entrega de acordo com
     * seu respectivo código
     *
     * @return array
     */
    protected function getMethods() {
        $servicesList = Mage::getStoreConfig('carriers/' . $this->_code . '/servicos');
        $arr = explode(',', $servicesList);

        return $arr;
    }

    /**
     * Verifica se as pré-condições para cálculo do frete estão satisfeitas
     *
     * @param Mage_Shipping_Model_Rate_Request $request Informações do pedido
     *
     * @return bool
     */
    protected function hasPreConditionsPassed(Mage_Shipping_Model_Rate_Request $request) {
        // ignora se o módulo estiver desabilitado
        if (!Mage::getStoreConfig('carriers/' . $this->_code . '/active')) {
            Mage::log('module disabled', null, "jadlog.log");

            return false;
        }

        // ignora se o país de destino não for o Brasil
        if ($request->getDestCountryId() != 'BR') {
            Mage::log('delivery address is outside Brazil', null, "jadlog.log");

            return false;
        }

        // ignora se a encomenda tem mais de 30kg

        /* if ($request->getPackageWeight() >= 30) {
          Mage::log('package is over 30 kilos');

          return false;
          } */

        return true;
    }

    /**
     * Adiciona a taxa adicional ao valor do frete
     *
     * @param string $rate Valor do frete
     *
     * @return mixed
     */
    protected function addTaxes($rate) {
        $taxType = Mage::getStoreConfig('carriers/' . $this->_code . '/tipo_taxa_adicional');

        if ($rate <= 0) {
            return 0;
        }

        // no tax
        if ($taxType <= 0) {
            return $rate;
        }

        $taxValue = Mage::getStoreConfig('carriers/' . $this->_code . '/taxa_adicional');

        // fixed value
        if ($taxType == 1) {
            return $rate + $taxValue;
        }

        // percentage
        if ($taxType == 2) {
            $taxValue = $rate * $taxValue / 100;

            return $rate + $taxValue;
        }
    }


    /**
     * Generate PAC weight
     */
    protected function _generateCubagem(){
        //Create PAC weight
        $pesoCubicoTotal = 0;

        // Get all visible itens from quote
        $items = Mage::getModel('checkout/cart')->getQuote()->getAllVisibleItems();

        foreach($items as $item){

            $while = 0;
            $itemAltura= 0;
            $itemLargura = 0;
            $itemComprimento = 0;

            $_product = $item->getProduct();

            if($_product->getData('volume_altura') == '' || (int)$_product->getData('volume_altura') == 0)
                $itemAltura = $this->getConfigData('altura_padrao');
            else
                $itemAltura = $_product->getData('volume_altura');

            if($_product->getData('volume_largura') == '' || (int)$_product->getData('volume_largura') == 0)
                $itemLargura = $this->getConfigData('largura_padrao');
            else
                $itemLargura = $_product->getData('volume_largura');

            if($_product->getData('volume_comprimento') == '' || (int)$_product->getData('volume_comprimento') == 0)
                $itemComprimento = $this->getConfigData('comprimento_padrao');
            else
                $itemComprimento = $_product->getData('volume_comprimento');

            while($while < $item->getQty()){
                $itemPesoCubico = 0;
                $itemPesoCubico = ($itemAltura * $itemLargura * $itemComprimento);
                $pesoCubicoTotal = $pesoCubicoTotal + $itemPesoCubico;
                $while ++;
            }
        }

        $this->_cubagem = number_format($pesoCubicoTotal, 2, '.', '');
    }

    /**
     * Returns the allowed carrier methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('title'));
    }

        /**
     * Check if current carrier offer support to tracking
     *
     * @return boolean true
     */
    public function isTrackingAvailable() {
        return true;
    }

    /**
     * Get Tracking Info
     *
     * @param mixed $tracking
     * @return mixed
     */
    public function getTrackingInfo($tracking) {

        $result = $this->getTracking($tracking);

        if ($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $result->getAllTrackings()) {
                    return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            $this->_getXmlTracking($trackings);
            return $result;
        }

        return false;
    }

    /**
     * Get Tracking
     *
     * @param array $trackings
     * @return Mage_Shipping_Model_Tracking_Result
     */
    public function getTracking($trackings) {
        $this->_result = Mage::getModel('shipping/tracking_result');
        foreach ((array) $trackings as $code) {
            $this->_getTracking($code);
        }
        return $this->_result;
    }

    /**
     * Protected Get Tracking, opens the request to Correios
     *
     * @param string $code
     * @return boolean
     */
    protected function _getTracking($code) {
        $error = Mage::getModel('shipping/tracking_result_error');
        $error->setTracking($code);
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('urlerror'));

        $cnpjContrante = Mage::getStoreConfig('carriers/' . $this->_code . '/cnpj_contrante');
        $senhaContrante = Mage::getStoreConfig('carriers/' . $this->_code . '/senha_contrante');

        $url = 'http://www.jadlog.com.br:8080';
        $url .= '/JadlogEdiWs/services/TrackingBean?method=consultar&CodCliente='.$cnpjContrante.'&Password='.$senhaContrante.'&NDs=' . $code;

        $debugData = array();

		try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $xmlResponse = curl_exec ($ch);
            $debugData['result'] = $xmlResponse;
            curl_close ($ch);
        }
        catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }

        $this->_parseXmlTrackingResponse($code, $xmlResponse);

        return $this->_result;
    }


    /**
     * Parse xml tracking response
     *
     * @param string $trackingvalue
     * @param string $response
     * @return null
     */
    protected function _parseXmlTrackingResponse($trackingvalue, $xmlResponse)
    {
        $errorTitle = 'Unable to retrieve tracking';
        $resultArr = array();
        $packageProgress = array();

 $envelope = simplexml_load_string($xmlResponse);
 $envelope->registerXPathNamespace('ns1','http://www.jadlog.com.br/JadlogWebService/services');

$nodes = $envelope->xpath('//Jadlog_Tracking_Consultar->ND');

		foreach($nodes as $node){
		  $xml = trim((string)$node);
		  $xml = simplexml_load_string($xml);
		}

/*
 $arr = array()
$arr[1] = $xml->Status;
*/
print_r($xml);

exit;
#echo $envelope->Jadlog_Tracking_Consultar->ND->Evento;
#		var_dump($xmlResponse);



        if ($xmlResponse) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->xpath("//TrackResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0][0];

            if($success===1){
                $arr = $xml->getXpath("//TrackResponse/Shipment/Service/Description/text()");
                $resultArr['service'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/PickupDate/text()");
                $resultArr['shippeddate'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/Weight/text()");
                $weight = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/UnitOfMeasurement/Code/text()");
                $unit = (string)$arr[0];

                $resultArr['weight'] = "{$weight} {$unit}";

                $activityTags = $xml->getXpath("//TrackResponse/Shipment/Package/Activity");
                if ($activityTags) {
                    $i=1;
                    foreach ($activityTags as $activityTag) {
                        $addArr=array();
                        if (isset($activityTag->ActivityLocation->Address->City)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->City;
                        }
                        if (isset($activityTag->ActivityLocation->Address->StateProvinceCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->StateProvinceCode;
                        }
                        if (isset($activityTag->ActivityLocation->Address->CountryCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->CountryCode;
                        }
                        $dateArr = array();
                        $date = (string)$activityTag->Date;//YYYYMMDD
                        $dateArr[] = substr($date,0,4);
                        $dateArr[] = substr($date,4,2);
                        $dateArr[] = substr($date,-2,2);

                        $timeArr = array();
                        $time = (string)$activityTag->Time;//HHMMSS
                        $timeArr[] = substr($time,0,2);
                        $timeArr[] = substr($time,2,2);
                        $timeArr[] = substr($time,-2,2);

                        if($i==1){
                           $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
                           $resultArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $resultArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           $resultArr['deliverylocation'] = (string)$activityTag->ActivityLocation->Description;
                           $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
                           if ($addArr) {
                            $resultArr['deliveryto']=implode(', ',$addArr);
                           }
                        }else{
                           $tempArr=array();
                           $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
                           $tempArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $tempArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           if ($addArr) {
                            $tempArr['deliverylocation']=implode(', ',$addArr);
                           }
                           $packageProgress[] = $tempArr;
                        }
                        $i++;
                    }
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
            }
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        $defaults = $this->getDefaults();

        if ($resultArr) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier($this->_code);
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
        } else {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
        }
        return $this->_result;
    }


}
