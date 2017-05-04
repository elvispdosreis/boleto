<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 15:58
 */

namespace Boleto\Bank;


class Hsbc extends AbstractBank implements InterfaceBank
{


    /**
     * @var \DateTime
     */
    private $vencimento = null;
    private $valor;
    private $codigocedente;
    private $nossonumero;
    private $codigobanco = '399';
    private $carteira = 'CNR';
    private $nummoeda = "9";

    private $codigobarras;

    function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero, $carteira, $cedente)
    {
        $this->setVencimento($vencimento);
        $this->setValor($valor);
        $this->setNossoNumero($nossonumero);
        $this->setCodigoCedente($cedente);

        //$codigo_banco_com_dv = $this->geraCodigoBanco($this->codigobanco);


        $valor = $this->getValorBoleto();
        $fatorvencimento = $this->fatorVencimento($this->vencimento);
        $juliano = $this->vencimentoJuliano($this->vencimento);

        $app = "2";

        // 43 numeros para o calculo do digito verificador do codigo de barras
        $linha = $this->codigobanco.$this->nummoeda.$fatorvencimento.$valor.$this->codigocedente.$this->nossonumero.$juliano.$app;

        $this->codigobarras = substr($linha,0,4).$this->dvBarra($linha).substr($linha,4,43);
    }


    public function getVencimento()
    {
        return $this->vencimento;
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function getNossoNumero()
    {
        // nosso número (com dvs) é 16 digitos
        return $this->geraNossoNumero($this->nossonumero,$this->codigocedente,$this->vencimento,'4');
    }

    public function getLinhaDigitavel()
    {
        return $this->linhaDigitavel($this->codigobarras);
    }

    public function getCodigoBarras()
    {
        return $this->codigobarras;
    }

    public function getCarteira()
    {
        return $this->carteira;
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
        //nosso número com maximo de 13 digitos
        $this->nossonumero = $this->formata_numero($nossonumero, 13, 0);
        return $this;
    }

    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    public function setCodigoCedente($cedente)
    {
        //codigo do cendente
        $this->codigocedente = $this->formata_numero($cedente,7,0);
        return $this;
    }

    public function getCodigoCedente()
    {
        return $this->codigocedente;
    }

    private function getValorBoleto()
    {
        return $this->formata_numero(number_format($this->valor, 2, ',', ''),10,0,"valor");
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


    //CONFERIDO
    protected function modulo_10($num)
    {
        $numtotal10 = 0;
        $fator = 2;

        // Separacao dos numeros
        for ($i = strlen($num); $i > 0; $i--) {
            // pega cada numero isoladamente
            $numeros[$i] = substr($num, $i - 1, 1);
            // Efetua multiplicacao do numero pelo (falor 10)
            // 2002-07-07 01:33:34 Macete para adequar ao Mod10 do Itaú
            $temp = $numeros[$i] * $fator;
            $temp0 = 0;
            foreach (preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v) {
                $temp0 += $v;
            }
            $parcial10[$i] = $temp0; //$numeros[$i] * $fator;
            // monta sequencia para soma dos digitos no (modulo 10)
            $numtotal10 += $parcial10[$i];
            if ($fator == 2) {
                $fator = 1;
            } else {
                $fator = 2; // intercala fator de multiplicacao (modulo 10)
            }
        }

        // várias linhas removidas, vide função original
        // Calculo do modulo 10
        $resto = $numtotal10 % 10;
        $digito = 10 - $resto;
        if ($resto == 0) {
            $digito = 0;
        }

        return $digito;
    }


    //CONFERIDO
    protected function modulo_11($num, $base = 9, $r = 0)
    {
        $soma = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                $fator = 1;
            }
            $fator++;
        }

        /* Calculo do modulo 11 */
        if ($r == 0) {
            $soma *= 10;
            $digito = $soma % 11;
            if ($digito == 10) {
                $digito = 0;
            }
            return $digito;
        } elseif ($r == 1) {
            $resto = $soma % 11;
            return $resto;
        }
    }


    public function linhaDigitavel($codigo)
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

    protected function geraNossoNumero($ndoc, $cedente, $venc, $tipoid)
    {
        $ndoc = $ndoc . $this->modulo_11_invertido($ndoc) . $tipoid;
        $venc = substr($venc, 0, 2) . substr($venc, 3, 2) . substr($venc, 8, 2);
        $res = $ndoc . $cedente . $venc;
        return $ndoc . $this->modulo_11_invertido($res);
    }



    protected function modulo_11_invertido($num)
    { // Calculo de Modulo 11 "Invertido" (com pesos de 9 a 2  e não de 2 a 9)
        $ftini = 2;
        $ftfim = 9;
        $fator = $ftfim;
        $soma = 0;

        for ($i = strlen($num); $i > 0; $i--) {
            $soma += substr($num, $i - 1, 1) * $fator;
            if (--$fator < $ftini) {
                $fator = $ftfim;
            };
        }

        $digito = $soma % 11;
        if ($digito > 9) {
            $digito = 0;
        }

        return $digito;
    }
}