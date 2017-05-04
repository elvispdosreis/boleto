<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:12
 */

require __DIR__ . '/../vendor/autoload.php';


use Boleto\Bank\Hsbc;

$vencimento = new DateTime('2017-05-08');

$boleto = new Hsbc($vencimento, 299, '1012948', 'CNR','4196252');


echo $boleto->getLinhaDigitavel();