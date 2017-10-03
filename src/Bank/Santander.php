<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 15:54
 */

namespace Boleto\Bank;


class Santander extends AbstractBank implements InterfaceBank
{
    private $vencimento;
    private $valor;
    private $agencia;
    private $conta;
    private $nossonumero;
    private $codigobarras;
    private $codigobanco = '033';
    private $carteira = '101';
    private $nummoeda = "9";
    private $fixo = "9";
    private $ios = "0";


    function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero, $carteira, $agencia, $conta)
    {
        $this->setVencimento($vencimento);
        $this->setValor($valor);
        $this->setNossoNumero($nossonumero);
        $this->setCarteira($carteira);
        $this->setAgencia($agencia);
        $this->setConta($conta);
        $valor = $this->getValorBoleto();
        $fatorvencimento = $this->fatorVencimento($this->vencimento);


        $dv = $this->codigobanco . $this->nummoeda . $fatorvencimento . $valor . $this->fixo . $this->conta . $this->nossonumero . $this->ios . $this->carteira;
        $this->codigobarras = $this->codigobanco . $this->nummoeda . $this->dvBarra($dv) . $fatorvencimento . $valor . $this->fixo . $this->conta . $this->nossonumero . $this->ios . $this->carteira;

    }

    function calcular()
    {
        $codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);


        //echo $linha = "BANCO[$this->codigobanco]MOEDA[$this->nummoeda]FATOR VENCIMENTO[$this->vencimento]VALOR[$this->valor]FIXO[$this->fixo]CODIGO CLIENTE[$this->conta]NOSSO NUMERO[$this->nossonumero]IOS[$this->ios]CARTEIRA[$this->carteira]<br><br>";

       // $agencia_codigo = $this->agencia . "/" . $this->conta . "/" . $this->dvBarra($linha);
        //
        //return array('nossonumero' => $this->nossonumero, 'codigobarras' => $barra, 'linhadigitavel' => $this->monta_linha_digitavel(substr($linha, 0, 4) . $this->dvBarra($linha) . substr($linha, 4)), 'agencia_codigo' => $agencia_codigo, 'codigo_banco_com_dv' => $codigo_banco_com_dv);
    }

    public function getVencimento()
    {
        return $this->vencimento;
    }

    public function getCarteira()
    {
        // TODO: Implement getCarteira() method.
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function getNossoNumero()
    {
        return $this->nossonumero;
    }

    public function getLinhaDigitavel()
    {
        return $this->linhaDigitavel($this->codigobarras);
    }

    public function getCodigoBarras()
    {
        return $this->codigobarras;
    }

    public function setVencimento(\DateTime $date)
    {
        $this->vencimento = $date;
    }

    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    public function setNossoNumero($nossonumero)
    {
        //nosso número (sem dv) é 11 digitos
        $digito = $this->formata_numero($nossonumero, 7, 0);
        //dv do nosso número
        $digito = $this->modulo_11($digito, 9, 0);
        //nosso número com maximo de 13 digitos
        $this->nossonumero = $this->formata_numero($nossonumero . $digito, 13, 0);
    }

    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    public function setAgencia($agencia)
    {
        //agencia é 4 digitos
        $this->formata_numero($agencia, 4, 0);
        return $this;
    }

    public function getAgencia()
    {
        return $this->agencia;
    }

    public function setConta($conta)
    {
        //conta é 7 digitos
        $this->conta = $this->formata_numero($conta, 7, 0);
        return $this;
    }

    private function getValorBoleto()
    {
        return $this->formata_numero(number_format($this->valor, 2, ',', ''),10,0,"valor");
    }

    //CORRIGIDO
    protected function linhaDigitavel($codigo)
    {
        // Posição 	Conteúdo
        // 1 a 3    Número do banco
        // 4        Código da Moeda - 9 para Real ou 8 - outras moedas
        // 5        Fixo "9'
        // 6 a 9    PSK - codigo cliente (4 primeiros digitos)
        // 10 a 12  Restante do PSK (3 digitos)
        // 13 a 19  7 primeiros digitos do Nosso Numero
        // 20 a 25  Restante do Nosso numero (8 digitos) - total 13 (incluindo digito verificador)
        // 26 a 26  IOS
        // 27 a 29  Tipo Modalidade Carteira
        // 30 a 30  Dígito verificador do código de barras
        // 31 a 34  Fator de vencimento (qtdade de dias desde 07/10/1997 até a data de vencimento)
        // 35 a 44  Valor do título

        // 1. Primeiro Grupo - composto pelo código do banco, código da moéda, Valor Fixo "9"
        // e 4 primeiros digitos do PSK (codigo do cliente) e DV (modulo10) deste campo
        $campo1 = substr($codigo, 0, 3) . substr($codigo, 3, 1) . substr($codigo, 19, 1) . substr($codigo, 20, 4);
        $campo1 = $campo1 . $this->modulo_10($campo1);
        $campo1 = substr($campo1, 0, 5) . '.' . substr($campo1, 5);


        // 2. Segundo Grupo - composto pelas 3 últimas posiçoes do PSK e 7 primeiros dígitos do Nosso Número
        // e DV (modulo10) deste campo
        $campo2 = substr($codigo, 24, 10);
        $campo2 = $campo2 . $this->modulo_10($campo2);
        $campo2 = substr($campo2, 0, 5) . '.' . substr($campo2, 5);


        // 3. Terceiro Grupo - Composto por : Restante do Nosso Numero (6 digitos), IOS, Modalidade da Carteira
        // e DV (modulo10) deste campo
        $campo3 = substr($codigo, 34, 10);
        $campo3 = $campo3 . $this->modulo_10($campo3);
        $campo3 = substr($campo3, 0, 5) . '.' . substr($campo3, 5);


        // 4. Campo - digito verificador do codigo de barras
        $campo4 = substr($codigo, 4, 1);


        // 5. Campo composto pelo fator vencimento e valor nominal do documento, sem
        // indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
        // tratar de valor zerado, a representacao deve ser 0000000000 (dez zeros).
        $campo5 = substr($codigo, 5, 4) . substr($codigo, 9, 10);

        return "$campo1 $campo2 $campo3 $campo4 $campo5";
    }

}