<?php

class Koan_Jadlog_Model_Carrier_Jadlog_Api
{

    protected $cepOrigem;
    protected $cepDestino;
    protected $cnpjContrante;
    protected $senhaContrante;
    protected $peso;
    protected $servico;
    protected $seguro;
    protected $valorDeclarado;
    protected $valorColeta;
    protected $freteApagar;
    protected $entrega;
    protected $webServiceUrl = 'http://www.jadlog.com.br:8080';
    protected $webServiceUrlPath = '/JadlogEdiWs/services/ValorFreteBean?method=valorar';
    public $resposta;

    /**
     * Filtra a string e retorna somente os números
     *
     * @param string $string String de entrada
     *
     * @return string
     */
    private function _somenteNumeros($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }

    /**
     * Retorna um valor formatado com duas casas decimais
     * Ex.: 10600
     *
     * @param string $valor String de entrada
     *
     * @return string
     */
    private function _formataValor($valor)
    {
        return sprintf("%01.2f", $valor);
    }

    /**
     * Retorna uma string formatada para as medidas de peso
     * para que fique com 3 casas decimais. Ex.: 1.000
     *
     * @param string $string String de entrada
     *
     * @return string
     */
    private function _formataPeso($string)
    {
        return sprintf("%01.3f", $string);
    }

    /**
     * Define o CEP de Origem
     *
     * @param string $cepOrigem CEP de Origem
     *
     */
    public function setCepOrigem($cepOrigem)
    {
        $this->cepOrigem = $this->_somenteNumeros($cepOrigem);

        return $this;
    }

    /**
     * Obtém o CEP de Origem
     *
     * @return string
     */
    public function getCepOrigem()
    {
        return $this->cepOrigem;
    }

    /**
     * Define o número do contrato
     *
     * @param string $contrato Nº do contrato com os correios
     *
     * @return $this
     */
    public function setContrato($cnpjContrante)
    {
        $this->contrato = $this->_somenteNumeros($cnpjContrante);

        return $this;
    }

    /**
     * Retorna o número do contrato
     *
     * @return string
     */
    public function getContrato()
    {
        return $this->contrato;
    }

    /**
     * Define a senha
     *
     * @param string $senha Senha
     *
     * @return $this
     */
    public function setSenha($senhaContrante)
    {
        $this->senha = $senhaContrante;

        return $this;
    }

    /**
     * Retorna a senha
     *
     * @return string
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * Define o CEP de Destino
     *
     * @param string $cepDestino CEP de Destino

     */
    public function setCepDestino($cepDestino)
    {
        $this->cepDestino = $this->_somenteNumeros($cepDestino);

        return $this;
    }

    /**
     * Obtém o CEP de Destino
     *
     * @return string
     */
    public function getCepDestino()
    {
        return $this->cepDestino;
    }


    public function setPeso($peso)
    {
        $this->peso = $peso;

        return $this;
    }


    public function getPeso()
    {
        return $this->peso;
    }



    public function setServico($servico)
    {

        $this->servico = $servico;

        return $this;
    }


    public function getServico()
    {
        return $this->servico;
    }


    public function setSeguro($seguro)
    {

        $this->seguro = $seguro;

        return $this;
    }


    public function getSeguro()
    {
        return $this->seguro;
    }


    public function setValorDeclarado($valorDeclarado)
    {
        $this->valorDeclarado = $this->_formataValor($valorDeclarado);

        return $this;
    }


    public function getValorDeclarado()
    {
        return $this->valorDeclarado;
    }



    public function setValorColeta($valorColeta)
    {
        $this->valorColeta = $this->_formataValor($valorColeta);

        return $this;
    }


    public function getValorColeta()
    {
        return $this->valorColeta;
    }


    public function setFreteApagar($freteApagar)
    {
        $this->freteApagar = $freteApagar;

        return $this;
    }

    public function getFreteApagar()
    {
        return $this->freteApagar;
    }



    public function setEntrega($entrega)
    {
        $this->entrega = $entrega;

        return $this;
    }

    public function getEntrega()
    {
        return $this->entrega;
    }

    /**
     * Junta a URL de WebService da Jadlog com as demais variáveis que
     * precisam ser enviadas.
     *
     * @return string URL do WebService + QueryString
     */
    public function getWebServiceUrl()
    {
        $url = $this->webServiceUrl . $this->webServiceUrlPath . '&';

        $params = array(
            "vModalidade"  			 => $this->getServico(),
            "Password"           => $this->getSenha(),
            "vSeguro"              => $this->getSeguro(),
            "vVlDec"  			 => $this->getValorDeclarado(),
            "vVlColeta"  		=> $this->getValorColeta(),
            "vCepOrig"        => $this->getCepOrigem(),
            "vCepDest"        	 => $this->getCepDestino(),
            "vPeso"       	 => $this->getPeso(),
            "vFrap"  			 => $this->getFreteApagar(),
            "vEntrega"  			 => $this->getEntrega(),
            "vCnpj"          	 => $this->getContrato()
        );

        return $url . http_build_query($params, '', '&');
    }

    /**
     * Conecta-se via cURL a um endereço e retorna a resposta
     *
     * @param string $url URL que será chamada
     *
     * @return string
     */
    private function _getDataFromUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        ob_start();
        curl_exec($ch);
        $response = ob_get_contents();
        ob_end_clean();

        return $response;
    }

    /**
     * Conecta-se aos correios e retorna o XML
     * com o resultado da consulta do frete
     *
     * @return string
     */
    public function conecta()
    {
        $url      = $this->getWebServiceUrl();
        $resposta = $this->_getDataFromUrl($url);

        return $resposta;
    }


    /**
     * Trata os dados recebidos pelo WS dos correios
     *
     * @throws Exception
     * @return array
     */
    public function dados()
    {
        $response = $this->conecta();

		$envelope = simplexml_load_string($response);
		$envelope->registerXPathNamespace('ns1','http://jadlogEdiws');

		$nodes = $envelope->xpath('//soapenv:Envelope/soapenv:Body/valorarResponse/ns1:valorarReturn');

		foreach($nodes as $node){
		  $xml = trim((string)$node);
		  $xml = simplexml_load_string($xml);
		}

        $resposta = array();
        $resposta['valor']             = str_replace(',', '.', $xml->Jadlog_Valor_Frete->Retorno);

        if ($xml !== false) {
	        $resposta['valor']             = str_replace(',', '.', $xml->Jadlog_Valor_Frete->Retorno);
            $resposta['servico']           = $xml->Jadlog_Valor_Frete->Codigo;
            $resposta['erro']              = $xml->Jadlog_Valor_Frete->Erro;
            $resposta['']           = $xml->Jadlog_Valor_Frete->Mensagem;
        } else {
            throw new \Exception('Resposta XML malformada');
        }

        return $resposta;
    }

    public function getModal($modal)
    {
        $modais = array(
            '0' => '6000',
            '3' => '3333',
            '4' => '3333',
            '5' => '3333',
            '6' => '3333',
            '7' => '6000',
            '9' => '6000',
            '10' => '6000',
            '12' => '6000',
            '14' => '3333',
        );

        return $modais[$modal];
    }

    public function getNomeServico($codigoServico)
    {
        $servicos = array(
            '0' => 'EXPRESSO',
            '3' => '.PACKAGE',
            '4' => 'RODOVIARIO',
            '5' => 'ECONOMICO',
            '6' => 'DOC',
            '7' => 'CORPORATE',
            '9' => '.COM',
            '10' => 'INTERNACIONAL',
            '12' => 'CARGO',
            '14' => 'EMERGENCIAL',
        );

        return $servicos[$codigoServico];
    }

}
