<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:00
 */

namespace Boleto\Bank;


class Bradesco extends AbstractBank
{

    public $vencimento = '';
    public $valor = '';
    public $agencia = '';
    public $agenciadigito = '';
    public $conta = '';
    public $contadigito = '';
    public $agenciacedente = '';
    public $agenciacedentedigito = '';
    public $contacedente = '';
    public $contacedentedigito = '';
    public $nossonumero = '';
    public $codigobanco = '237';
    public $carteira = '09';
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
        //conta é 6 digitos
        $this->conta = $this->formata_numero($this->conta, 6, 0);
        //dv da conta
        $conta_dv = $this->formata_numero($this->contadigito, 1, 0);
        //carteira é 2 caracteres

        //nosso número (sem dv) é 11 digitos
        $nnum = $this->formata_numero($this->carteira, 2, 0) . $this->formata_numero($this->nossonumero, 11, 0);
        //dv do nosso número
        $dv_nosso_numero = $this->digitoVerificador_nossonumero($nnum);

        //conta cedente (sem dv) é 7 digitos
        $this->contacedente = $this->formata_numero($this->contacedente, 7, 0);
        //dv da conta cedente
        $this->contacedentedigito = $this->formata_numero($this->contacedentedigito, 1, 0);

        //$ag_contacedente = $agencia . $conta_cedente;

        // 43 numeros para o calculo do digito verificador do codigo de barras
        $dv = $this->digitoVerificador_barra("$this->codigobanco$this->nummoeda$this->vencimento$this->valor$this->agencia$nnum$this->contacedente" . '0', 9, 0);
        // Numero para o codigo de barras com 44 digitos
        $linha = "$this->codigobanco$this->nummoeda$dv$this->vencimento$this->valor$this->agencia$nnum$this->contacedente" . "0";

        $this->nossonumero = substr($nnum, 0, 2) . '/' . substr($nnum, 2) . '-' . $dv_nosso_numero;
        $agencia_codigo = $this->agencia . "-" . $this->agenciadigito . " / " . $this->contacedente . "-" . $this->contacedentedigito;

        return array('nossonumero' => $this->nossonumero, 'codigobarras' => $linha, 'linhadigitavel' => $this->monta_linha_digitavel($linha), 'agencia_codigo' => $agencia_codigo, 'codigo_banco_com_dv' => $codigo_banco_com_dv);
    }

    protected function digitoVerificador_nossonumero($numero)
    {
        $resto2 = $this->modulo_11($numero, 7, 1);
        $digito = 11 - $resto2;
        if ($digito == 10) {
            $dv = "P";
        } elseif ($digito == 11) {
            $dv = 0;
        } else {
            $dv = $digito;
        }
        return $dv;
    }


    protected function monta_linha_digitavel($codigo)
    {
        // 01-03    -> Código do banco sem o digito
        // 04-04    -> Código da Moeda (9-Real)
        // 05-05    -> Dígito verificador do código de barras
        // 06-09    -> Fator de vencimento
        // 10-19    -> Valor Nominal do Título
        // 20-44    -> Campo Livre (Abaixo)
        // 20-23    -> Código da Agencia (sem dígito)
        // 24-05    -> Número da Carteira
        // 26-36    -> Nosso Número (sem dígito)
        // 37-43    -> Conta do Cedente (sem dígito)
        // 44-44    -> Zero (Fixo)

        // 1. Campo - composto pelo código do banco, código da moéda, as cinco primeiras posições
        // do campo livre e DV (modulo10) deste campo

        $p1 = substr($codigo, 0, 4);                            // Numero do banco + Carteira
        $p2 = substr($codigo, 19, 5);                        // 5 primeiras posições do campo livre
        $p3 = $this->modulo_10("$p1$p2");                        // Digito do campo 1
        $p4 = "$p1$p2$p3";                                // União
        $campo1 = substr($p4, 0, 5) . '.' . substr($p4, 5);

        // 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 24, 10);                        //Posições de 6 a 15 do campo livre
        $p2 = $this->modulo_10($p1);                                //Digito do campo 2
        $p3 = "$p1$p2";
        $campo2 = substr($p3, 0, 5) . '.' . substr($p3, 5);

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($codigo, 34, 10);                        //Posições de 16 a 25 do campo livre
        $p2 = $this->modulo_10($p1);                                //Digito do Campo 3
        $p3 = "$p1$p2";
        $campo3 = substr($p3, 0, 5) . '.' . substr($p3, 5);

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($codigo, 4, 1);

        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
        $p1 = substr($codigo, 5, 4);
        $p2 = substr($codigo, 9, 10);
        $campo5 = "$p1$p2";

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }
}