<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:00
 */

namespace Boleto\Bank;


class Brasil extends AbstractBank implements InterfaceBank
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


    function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero = null, $carteira = null, $agencia = null, $conta = null, $convenio = null, $contrato = null)
    {
        $this->setVencimento($vencimento);
        $this->setValor($valor);
        $this->setNossoNumero($nossonumero);
        $this->setCarteira($carteira);
        $this->setAgencia($agencia);
        $this->setConta($conta);
        $this->setConvenio($convenio);
        $this->setContrato($contrato);
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

    public function setNossoNumero($nossonumero)
    {
        $this->nossonumero = $nossonumero;
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
        return $this->agencia . "-" . $this->agenciadigito . " / " . $this->contacedente . "-" . $this->contacedentedigito;
    }

    public function setConta($conta)
    {
        $this->conta = $this->formata_numero($conta, 8, 0);
        return $this;
    }

    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    public function setContrato($contrato)
    {
        $this->contrato = $contrato;
        return $this;
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
        /*
        if ($this->formatacaoconvenio == "8") {
            $this->convenio = $this->formata_numero($this->convenio,8,0,"convenio");
            $this->nossonumero = $this->formata_numero($this->nossonumero,9,0);
            $this->nossonumero = $this->convenio . $this->nossonumero ."-". $this->modulo_11($this->convenio.$this->nossonumero);
        }
        // Carteira 18 com Convênio de 7 dígitos
        if ($this->formatacaoconvenio == "7") {
            $this->convenio = $this->formata_numero($this->convenio,7,0,"convenio");
            $this->nossonumero = $this->formata_numero($this->nossonumero,10,0);
            $this->nossonumero = $this->convenio.$this->nossonumero;
        }
        // Carteira 18 com Convênio de 6 dígitos
        if ($this->formatacaoconvenio == "6") {
            $this->convenio = $this->formata_numero($this->convenio,6,0,"convenio");
            if ($this->formatacaonossonumero == "1") {
                $this->nossonumero = $this->convenio . $this->nossonumero ."-". $this->modulo_11($this->convenio.$this->nossonumero);
            }
            if ($this->formatacaonossonumero == "2") {
                $this->nossonumero = $this->formata_numero($this->nossonumero,17,0);
            }
        }
        */
        return $this->nossonumero;
    }

    public function getLinhaDigitavel()
    {
        return $this->linhaDigitavel($this->getCodigoBarras());
    }

    public function getCodigoBarras()
    {
        $valor = $this->getValorBoleto();
        $fatorvencimento = $this->fatorVencimento($this->vencimento);

        $codigobarras = '';

        // Convênio de 7 dígitos
        if (strlen($this->contrato) == "7") {
            $this->convenio = $this->formata_numero($this->convenio, 7, 0, "convenio");
            // Nosso número de até 10 dígitos
            $this->nossonumero = $this->formata_numero($this->nossonumero, 10, 0);
            $dv = $this->dvBarra($this->codigobanco . $this->nummoeda . $fatorvencimento . $valor . '000000' . $this->contrato . $this->nossonumero . $this->carteira);
            $codigobarras = $this->codigobanco . $this->nummoeda . $dv . $fatorvencimento . $valor . '000000' . $this->contrato . $this->nossonumero . $this->carteira;
        }

        /*
        // Carteira 18 com Convênio de 8 dígitos
        if (strlen($this->contrato) == "8") {
            $this->convenio = $this->formata_numero($this->convenio, 8, 0, "convenio");
            // Nosso número de até 9 dígitos
            $this->nossonumero = $this->formata_numero($this->nossonumero, 9, 0);
            $dv = $this->dvBarra($this->codigobanco . $this->nummoeda . $fatorvencimento . $this->valor . '000000' . $this->contrato . $this->nossonumero . $this->carteira);
            $codigobarras = $this->codigobanco . $this->nummoeda . $dv . $fatorvencimento . $valor . '000000' . $this->contrato . $this->nossonumero . $this->carteira;
        }

        // Carteira 18 com Convênio de 6 dígitos
        if (strlen($this->contrato) == "6") {
            $this->convenio = $this->formata_numero($this->convenio, 6, 0, "convenio");
            if ($this->formatacaonossonumero == "1") {
                // Nosso número de até 5 dígitos
                $this->nossonumero = $this->formata_numero($this->nossonumero, 5, 0);
                $dv = $this->dvBarra($this->codigobanco . $this->nummoeda . $fatorvencimento . $valor . $this->contrato . $this->nossonumero . $this->agencia . $this->conta . $this->carteira);
                $codigobarras = $this->codigobanco . $this->nummoeda . $dv . $fatorvencimento . $valor . $this->contrato . $this->nossonumero . $this->agencia . $this->conta . $this->carteira;
            }
            if ($this->formatacaonossonumero == "2") {
                // Nosso número de até 17 dígitos
                $this->nossonumero = $this->formata_numero($this->nossonumero, 10, 0);
                $dv = $this->dvBarra($this->codigobanco . $this->nummoeda . $fatorvencimento . $valor . $this->contrato . $this->nossonumero);
                $codigobarras = $this->codigobanco . $this->nummoeda . $dv . $fatorvencimento . $valor . $this->contrato . $this->nossonumero;
            }
        }
        */
        return $codigobarras;
    }

    private function getValorBoleto()
    {
        return $this->formata_numero(number_format($this->valor, 2, ',', ''),10,0,"valor");
    }
}
