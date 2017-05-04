<?php
require __DIR__ . '/../vendor/autoload.php';


use Boleto\Bank\Bradesco;

$vencimento = new DateTime('2017-05-08');

$boleto = new Bradesco($vencimento, 490.71, '505337', '26','3385', '604', '604');


echo $boleto->getLinhaDigitavel();