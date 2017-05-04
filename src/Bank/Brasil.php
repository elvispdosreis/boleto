<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:00
 */

namespace Boleto\Bank;


class Brasil extends AbstractBank
{
    public $vencimento = '';
    public $valor = '';
    public $agencia = '';
    public $agenciadigito = '';
    public $conta = '';
    public $contadigito = '';
    public $convenio = ''; // Num do convênio - REGRA: 6 ou 7 ou 8 dígitos
    public $contrato = ''; //// Num do seu contrato
    public $variacaocarteira = '-019';  // Variação da Carteira, com traço (opcional)
    public $formatacaoconvenio = '7'; // REGRA: 8 p/ Convênio c/ 8 dígitos, 7 p/ Convênio c/ 7 dígitos, ou 6 se Convênio c/ 6 dígitos
    public $formatacaonossonumero = '2'; // REGRA: Usado apenas p/ Convênio c/ 6 dígitos: informe 1 se for NossoNúmero de até 5 dígitos ou 2 para opção de até 17 dígitos
    public $nossonumero = '';
    public $codigobanco = '001';
    public $carteira = '06';
    public $nummoeda = "9";

    function __construct(){
    }

    function calcular(){

        $codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);

        $this->vencimento = $this->fator_vencimento($this->vencimento);

        //valor tem 10 digitos, sem virgula
        $this->valor = $this->formata_numero(number_format($this->valor, 2, ',', ''),10,0,"valor");
        //agencia é sempre 4 digitos
        $this->agencia = $this->formata_numero($this->agencia,4,0);
        //conta é sempre 8 digitos
        $this->conta = $this->formata_numero($this->conta,8,0);
        //agencia e conta
        $agencia_codigo = $this->agencia."-". $this->modulo_11($this->agencia) ." / ". $this->conta ."-". $this->modulo_11($this->conta);
        //Zeros: usado quando convenio de 7 digitos
        $livre_zeros='000000';

        // Carteira 18 com Convênio de 8 dígitos
        if ($this->formatacaoconvenio == "8") {
            $this->convenio = $this->formata_numero($this->convenio,8,0,"convenio");
            // Nosso número de até 9 dígitos
            $this->nossonumero = $this->formata_numero($this->nossonumero,9,0);
            $dv=$this->modulo_11("$this->codigobanco$this->nummoeda$this->vencimento$this->valor$livre_zeros$this->convenio$this->nossonumero$this->carteira");
            $linha="$this->codigobanco$this->nummoeda$dv$this->vencimento$this->valor$livre_zeros$this->convenio$this->nossonumero$this->carteira";
            //montando o nosso numero que aparecerá no boleto
            $this->nossonumero = $this->convenio . $this->nossonumero ."-". $this->modulo_11($this->convenio.$this->nossonumero);
        }

        // Carteira 18 com Convênio de 7 dígitos
        if ($this->formatacaoconvenio == "7") {
            $this->convenio = $this->formata_numero($this->convenio,7,0,"convenio");
            // Nosso número de até 10 dígitos
            $this->nossonumero = $this->formata_numero($this->nossonumero,10,0);

            $dv=$this->modulo_11("$this->codigobanco$this->nummoeda$this->vencimento$this->valor$livre_zeros$this->convenio$this->nossonumero$this->carteira");
            $linha="$this->codigobanco$this->nummoeda$dv$this->vencimento$this->valor$livre_zeros$this->convenio$this->nossonumero$this->carteira";
            $this->nossonumero = $this->convenio.$this->nossonumero;
            //Não existe DV na composição do nosso-número para convênios de sete posições
        }

        // Carteira 18 com Convênio de 6 dígitos
        if ($this->formatacaoconvenio == "6") {
            $this->convenio = $this->formata_numero($this->convenio,6,0,"convenio");

            if ($this->formatacaonossonumero == "1") {

                // Nosso número de até 5 dígitos
                $this->nossonumero = $this->formata_numero($this->nossonumero,5,0);
                $dv = $this->modulo_11("$this->codigobanco$this->nummoeda$this->vencimento$this->valor$this->convenio$this->nossonumero$this->agencia$this->conta$this->carteira");
                $linha = "$this->codigobanco$this->nummoeda$dv$this->vencimento$this->valor$this->convenio$this->nossonumero$this->agencia$this->conta$this->carteira";
                //montando o nosso numero que aparecerá no boleto
                $this->nossonumero = $this->convenio . $this->nossonumero ."-". $this->modulo_11($this->convenio.$this->nossonumero);
            }

            if ($this->formatacaonossonumero == "2") {
                // Nosso número de até 17 dígitos
                $nservico = "21";
                $this->nossonumero = $this->formata_numero($this->nossonumero,17,0);
                $dv = $this->modulo_11("$this->codigobanco$this->nummoeda$this->vencimento$this->valor$this->convenio$this->nossonumero$nservico");
                $linha = "$this->codigobanco$this->nummoeda$dv$this->vencimento$this->valor$this->convenio$this->nossonumero$nservico";
            }
        }

        return array('nossonumero'=>$this->nossonumero, 'codigobarras'=>$linha, 'linhadigitavel'=>$this->monta_linha_digitavel($linha), 'agencia_codigo'=>$agencia_codigo, 'codigo_banco_com_dv'=>$codigo_banco_com_dv);
    }

    protected function modulo_10($num) {
        $numtotal10 = 0;
        $fator = 2;

        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num,$i-1,1);
            $parcial10[$i] = $numeros[$i] * $fator;
            $numtotal10 .= $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            }
            else {
                $fator = 2;
            }
        }

        $soma = 0;
        for ($i = strlen($numtotal10); $i > 0; $i--) {
            $numeros[$i] = substr($numtotal10,$i-1,1);
            $soma += $numeros[$i];
        }
        $resto = $soma % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }

        return $digito;
    }

    protected function modulo_11($num, $base=9, $r=0) {
        $soma = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num,$i-1,1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;

            //corrigido
            if ($digito == 10) {
                $digito = "X";
            }

            if (strlen($num) == "43") {
                //então estamos checando a linha digitável
                if ($digito == "0" or $digito == "X" or $digito > 9) {
                    $digito = 1;
                }
            }
            return $digito;
        }
        elseif ($r == 1){
            $resto = $soma % 11;
            return $resto;
        }
    }

    /*
    Montagem da linha digitável - Função tirada do PHPBoleto
    Não mudei nada
    */
    protected function monta_linha_digitavel($linha) {
        // Posição 	Conteúdo
        // 1 a 3    Número do banco
        // 4        Código da Moeda - 9 para Real
        // 5        Digito verificador do Código de Barras
        // 6 a 19   Valor (12 inteiros e 2 decimais)
        // 20 a 44  Campo Livre definido por cada banco

        // 1. Campo - composto pelo código do banco, código da moéda, as cinco primeiras posições
        // do campo livre e DV (modulo10) deste campo
        $p1 = substr($linha, 0, 4);
        $p2 = substr($linha, 19, 5);
        $p3 = $this->modulo_10("$p1$p2");
        $p4 = "$p1$p2$p3";
        $p5 = substr($p4, 0, 5);
        $p6 = substr($p4, 5);
        $campo1 = "$p5.$p6";

        // 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 24, 10);
        $p2 = $this->modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo2 = "$p4.$p5";

        // 3. Campo composto pelas posicoes 16 a 25 do campo livre
        // e livre e DV (modulo10) deste campo
        $p1 = substr($linha, 34, 10);
        $p2 = $this->modulo_10($p1);
        $p3 = "$p1$p2";
        $p4 = substr($p3, 0, 5);
        $p5 = substr($p3, 5);
        $campo3 = "$p4.$p5";

        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($linha, 4, 1);

        // 5. Campo composto pelo valor nominal pelo valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 000 (tres zeros).
        $campo5 = substr($linha, 5, 14);

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }

}