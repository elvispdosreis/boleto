<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:14
 */

namespace Boleto\Bank;


interface InterfaceBank
{
    public function getVencimento();
    public function getCarteira();
    public function getValor();
    public function getNossoNumero();
    public function getLinhaDigitavel();
    public function getCodigoBarras();
}