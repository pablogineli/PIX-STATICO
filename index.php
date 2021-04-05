<?php
require __DIR__ . "/vendor/autoload.php";


use \App\Pix\Payload;
use \Mpdf\QrCode\QrCode;
use \Mpdf\QrCode\Output;


//instancia principal do PAYLOAD
$objPix = (new Payload) ->setPixKey('CHAVE PIX')
                        ->setDescription('')
                        ->setMerchantName('NOME')
                        ->setMerchantCity('SAO.PAULO')
                        ->setAmount(30.00)
                        ->setTxid('***');

// Objeto com a chave PIX
$payLoadQrCode = $objPix->getPayload();

//QRCode com Chave PIX
$objQrcode = new QrCode($payLoadQrCode);
$image = (new Output\Png)->output($objQrcode, 400);

//header('content-type: image/png');
//echo $image;
?>

<h1>QR Code Pix</h1>

<br>

<img src="data:image/png;base64, <?=base64_encode($image)?>"

     <br>
     <br>

Codigo Pix<br>
<strong><?=$payLoadQrCode?></strong>
