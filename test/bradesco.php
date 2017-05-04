<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:12
 */

require __DIR__ . '/config.php';
require __DIR__ . '/../vendor/autoload.php';


use Boleto\Bank\Bradesco;

$boleto = new Bradesco();
$boleto->vencimento = new DateTime();
$boleto->valor = 1271.01;
$boleto->agencia = $row["agencia"];
$boleto->agenciadigito = $row["ag_digito"];
$boleto->conta = $row["conta"];
$boleto->carteira = $row["carteira"];
$boleto->contadigito = $row["cc_digito"];
$boleto->agenciacedente = $row["agencia"];
$boleto->agenciacedentedigito = $row["ag_digito"];
$boleto->contacedente = $row["conta"];
$boleto->contacedentedigito = $row["cc_digito"];
$boleto->nossonumero = $row['id_venda'];