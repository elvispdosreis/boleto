<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 15:55
 */

namespace Boleto\Bank;


class Itau extends AbstractBank
{
    public $vencimento = '';
    public $valor = '';
    public $agencia = '';
    public $conta = '';
    public $nossonumero = '';
    public $codigobanco = '341';
    public $carteira = '175';
    public $nummoeda = "9";

    function __construct()
    {

    }

    function calcular()
    {
        $codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);

        $this->vencimento = $this->fator_vencimento($this->vencimento);

        //valor tem 10 digitos, sem virgula
        $this->valor = $this->formata_numero(number_format($this->valor, 2, ',', ''), 10, 0, "valor");
        //agencia é 4 digitos
        $this->agencia = $this->formata_numero($this->agencia, 4, 0);
        //conta é 7 digitos
        $this->conta = $this->formata_numero($this->conta, 5, 0);

        //nosso número com maximo de 13 digitos
        $this->nossonumero = $this->formata_numero($this->nossonumero, 8, 0);

        $codigo_barras = $this->codigobanco . $this->nummoeda . $this->vencimento . $this->valor . $this->carteira . $this->nossonumero . $this->modulo_10($this->agencia . $this->conta . $this->carteira . $this->nossonumero) . $this->agencia . $this->conta . $this->modulo_10($this->agencia . $this->conta) . '000';

        $linha = substr($codigo_barras, 0, 4) . $this->dvBarra($codigo_barras) . substr($codigo_barras, 4, 43);
        $agencia_codigo = $this->agencia . "/" . $this->conta . "-" . $this->modulo_10($this->agencia . $this->conta);
        $this->nossonumero = $this->carteira . '/' . $this->nossonumero . '-' . $this->modulo_10($this->agencia . $this->conta . $this->carteira . $this->nossonumero);

        return array('nossonumero' => $this->nossonumero, 'codigobarras' => $linha, 'linhadigitavel' => $this->monta_linha_digitavel($linha), 'agencia_codigo' => $agencia_codigo, 'codigo_banco_com_dv' => $codigo_banco_com_dv);

    }

    protected function dvBarra($numero)
    {
        $resto2 = $this->modulo_11($numero, 9, 1);
        $digito = 11 - $resto2;
        if ($digito == 0 || $digito == 1 || $digito == 10 || $digito == 11) {
            $dv = 1;
        } else {
            $dv = $digito;
        }
        return $dv;
    }




    protected function monta_linha_digitavel($codigo)
    {
        // campo 1
        $banco = substr($codigo, 0, 3);
        $moeda = substr($codigo, 3, 1);
        $ccc = substr($codigo, 19, 3);
        $ddnnum = substr($codigo, 22, 2);
        $dv1 = $this->modulo_10($banco . $moeda . $ccc . $ddnnum);
        // campo 2
        $resnnum = substr($codigo, 24, 6);
        $dac1 = substr($codigo, 30, 1);//modulo_10($agencia.$conta.$carteira.$nnum);
        $dddag = substr($codigo, 31, 3);
        $dv2 = $this->modulo_10($resnnum . $dac1 . $dddag);
        // campo 3
        $resag = substr($codigo, 34, 1);
        $contadac = substr($codigo, 35, 6); //substr($codigo,35,5).modulo_10(substr($codigo,35,5));
        $zeros = substr($codigo, 41, 3);
        $dv3 = $this->modulo_10($resag . $contadac . $zeros);
        // campo 4
        $dv4 = substr($codigo, 4, 1);
        // campo 5
        $fator = substr($codigo, 5, 4);
        $valor = substr($codigo, 9, 10);

        $campo1 = substr($banco . $moeda . $ccc . $ddnnum . $dv1, 0, 5) . '.' . substr($banco . $moeda . $ccc . $ddnnum . $dv1, 5, 5);
        $campo2 = substr($resnnum . $dac1 . $dddag . $dv2, 0, 5) . '.' . substr($resnnum . $dac1 . $dddag . $dv2, 5, 6);
        $campo3 = substr($resag . $contadac . $zeros . $dv3, 0, 5) . '.' . substr($resag . $contadac . $zeros . $dv3, 5, 6);
        $campo4 = $dv4;
        $campo5 = $fator . $valor;

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }

}