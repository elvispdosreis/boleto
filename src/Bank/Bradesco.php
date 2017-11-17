<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:00
 */

namespace Boleto\Bank;


use Boleto\Entity\Beneficiario;
use Boleto\Entity\Pagador;

class Bradesco extends AbstractBank implements InterfaceBank
{



    /**
     * @var \DateTime
     */
    private $vencimento;
    private $valor;
    private $agencia;
    private $agenciadigito;
    private $conta;
    private $contadigito;
    private $contacedente;
    private $contacedentedigito;
    private $nossonumero;
    private $codigobanco = '237';
    private $carteira = '09';
    private $nummoeda = "9";

    function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero, $carteira, $agencia, $conta, $contacedente)
    {
        $this->setVencimento($vencimento);
        $this->setValor($valor);
        $this->setNossoNumero($nossonumero, $carteira);
        $this->setCarteira($carteira);
        $this->setAgencia($agencia);
        $this->setConta($conta);
        $this->setContaCedente($contacedente);

        $valor = $this->getValorBoleto();
        $fatorvencimento = $this->fatorVencimento($this->vencimento);

        // 43 numeros para o calculo do digito verificador do codigo de barras
        $dv = $this->dvBarra($this->codigobanco.$this->nummoeda.$fatorvencimento.$valor.$this->agencia.$this->nossonumero.$this->contacedente.'0', 9, 0);

        // Numero para o codigo de barras com 44 digitos
        $this->codigobarras = $this->codigobanco.$this->nummoeda.$dv.$fatorvencimento.$valor.$this->agencia.$this->nossonumero.$this->contacedente.'0';
    }

    public function getVencimento()
    {
        return $this->vencimento;
    }

    public function getCarteira()
    {
        return $this->carteira;
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function getNossoNumero()
    {
        //dv do nosso número
        $dv = $this->dvNossonumero($this->nossonumero);
        return substr($this->nossonumero, 0, 2) . '/' . substr($this->nossonumero, 2) . '-' . $dv;
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
        return $this;
    }

    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    public function setNossoNumero($nossonumero, $carteira = null)
    {
        if(is_null($carteira)){
            $carteira = $this->carteira;
        }
        //nosso número (sem dv) é 11 digitos
        $this->nossonumero = $this->formata_numero($carteira, 2, 0) . $this->formata_numero($nossonumero, 11, 0);
        return $this;
    }

    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    public function setAgencia($agencia)
    {
        //agencia é 4 digitos
        $this->agencia = $this->formata_numero($agencia, 4, 0);
        return $this;
    }

    public function getAgencia()
    {
        return  $this->agencia . "-" . $this->agenciadigito . " / " . $this->contacedente . "-" . $this->contacedentedigito;
    }

    public function setConta($cedente)
    {
        //conta é 6 digitos
        $this->conta = $this->formata_numero($this->conta, 6, 0);
        return $this;
    }

    public function getConta()
    {
        return $this->conta;
    }

    public function setContaCedente($contacedente)
    {
        //conta cedente (sem dv) é 7 digitos
        $this->contacedente = $this->formata_numero($contacedente, 7, 0);
        return $this;
    }

    public function getContaCedente()
    {
        return $this->contacedente;
    }

    public function setContaCedenteDigito($contacedentedigito)
    {
        //dv da conta cedente
        $this->contacedentedigito = $this->formata_numero($contacedentedigito, 1, 0);
        return $this;
    }

    public function getContaCedenteDigito()
    {
        return $this->contacedente;
    }

    private function getValorBoleto()
    {
        return $this->formata_numero(number_format($this->valor, 2, ',', ''),10,0,"valor");
    }

    protected function dvNossonumero($numero)
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

    protected function dvBarra($numero) {
        $resto2 = $this->modulo_11($numero, 9, 1);
        if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
            $dv = 1;
        } else {
            $dv = 11 - $resto2;
        }
        return $dv;
    }


    protected function linhaDigitavel($codigo)
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