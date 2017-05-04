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
    public function setVencimento(\DateTime $date);
    public function getVencimento();
    public function setCarteira($carteira);
    public function getCarteira();
    public function setValor($valor);
    public function setNossoNumero($nossonumero);
    public function getLinhaDigitavel();
    public function getCodigoBarras();
    public function linhaDigitavel($codigo);
    public function getValorBoleto();
}