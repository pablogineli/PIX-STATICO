<?php


namespace App\Pix;


class Payload
{

    /**
    * IDs do Payload
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /**
     * chave PIX.
     * @param string $pixKey
     */
    private $pixKey;

    /**
     * Descrição do pagamento.
     * @param string $description
     */
    private $description;

    /**
     * Nome do titular da conta.
     * @param string $merchantName
     */
    private $merchantName;

    /**
     * Cidade do titular da conta.
     * @param string $merchantCity
     */
    private $merchantCity;

    /**
     * ID transação PIX.
     * @param string $txid
     */
    private $txid;

    /**
     * Valor da transação.
     * @param string $amount
     */
    private $amount;


    /**
     * Metado responsavel por definir valor de $pixKey
     * @param string $pixKey
     */
    public function setPixKey(string $pixKey){
        $this->pixKey = $pixKey;
        return $this;
    }


    /**
     * Metado responsavel por definir valor de $description.
     * @param string $description
     */
    public function setDescription(string $description){
        $this->description = $description;
        return $this;
    }

    /**
     * Metado responsalvel por definir valor de $merchantName.
     * @param string $merchantName
     */
    public function setMerchantName(string $merchantName){
        $this->merchantName = $merchantName;
        return $this;
    }

    /**
     * Metado responsalvel por definir valor de $merchantCity.
     * @param  string $merchantCity
     */
    public function setMerchantCity(string $merchantCity){
        $this->merchantCity = $merchantCity;
        return $this;
    }

    /**
     * Metodo responsavel por definir valor de $txid.
     * @param string $pixKey
     */
    public function setTxid($txid){
        $this->txid = $txid;
        return $this;
    }

    /**
     *
     * @param string $amount
     */
    public function setAmount(string $amount){
        $this->amount = number_format($amount,2,'.','');
        return $this;
    }

    /**
     * Responsavel por retornar o valor completo do payload.
     * @param string $id
     * @param string $value
     * return string $id.$size.$valeu
     */
    private function getValue($id, $value){
        $size = str_pad(strlen($value),2,'0',STR_PAD_LEFT);
        return $id.$size.$value;
    }

    /**
     * Responsavel por retornar os valores completos da informação da conta.
     * @return string
     */
    private function getMerchantAccountInformation(){
        //DOMINIO do banco
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI,'BR.GOV.BCB.PIX');
        //CHAVE PIX
        $key = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY,$this->pixKey);
        // Descrição do pagamento
        $description =  strlen($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->description) : '';

        //Retorna o valor complento da conta
        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION,$gui.$key.$description);
    }

    /**
     * Metodo responsalve por retornar os valores completos do campo adicional
     */
    private function getAdditionalDataFieldTemplate(){
        //TXID
        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->txid);
        //Retorna o valor completo
        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $txid);
    }

    /**
     * Metodo responsavel por gerar Codigo Completo do Payload
     * @return string
     */
    public function getPayLoad(){
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR,'01').
                    $this->getMerchantAccountInformation().
                    $this->getValue(self::ID_MERCHANT_CATEGORY_CODE,'0000').
                    $this->getValue(self::ID_TRANSACTION_CURRENCY, '986').
                    $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->amount).
                    $this->getValue(self::ID_COUNTRY_CODE, 'BR').
                    $this->getValue(self::ID_MERCHANT_NAME,$this->merchantName).
                    $this->getValue(self::ID_MERCHANT_CITY, $this->merchantCity).
                    $this->getAdditionalDataFieldTemplate();

        return $payload.$this->getCRC16($payload);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload) {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
    }

}
