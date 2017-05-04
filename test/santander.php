<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:12
 */


require __DIR__ . '/../vendor/autoload.php';


use Boleto\Bank\Santander;

$boleto = new Santander();
$boleto->vencimento = new DateTime('2017-05-08');
$boleto->valor = 100;
$boleto->agencia = '';
$boleto->conta = '';
$boleto->carteira = '';
$boleto->setNossoNumero(1111111111);