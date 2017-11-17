<?php

namespace Boleto\Bank;

use Boleto\Entity\Beneficiario;
use Boleto\Entity\Pagador;

/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 15:52
 */
class AbstractBank
{
    private $pagador;
    private $beneficiario;
    private $demostrativo;
    private $instrucao;

    /**
     * @param Pagador $pagador
     * @return $this
     */
    public function setPagador(Pagador $pagador)
    {
        $this->pagador = $pagador;
        return $this;
    }

    /**
     * @return Pagador
     */
    public function getPagador()
    {
        return $this->pagador;
    }

    /**
     * @return Beneficiario
     */
    public function getBeneficiario()
    {
        return $this->beneficiario;
    }

    /**
     * @param Beneficiario $beneficiario
     * @return $this
     */
    public function setBeneficiario(Beneficiario $beneficiario)
    {
        $this->beneficiario = $beneficiario;
        return $this;
    }

    /**
     * @param string[] $demostrativo
     * @return $this
     */
    public function setDemostrativo($demostrativo)
    {
        array_push($this->demostrativo, $demostrativo);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDemostrativo()
    {
        return $this->demostrativo;
    }


    /**
     * @param string[] $instrucao
     * @return $this
     */
    public function setInstrucao($instrucao)
    {
        array_push($this->instrucao, $instrucao);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getInstrucao()
    {
        return $this->instrucao;
    }


    // protected $valor;
    //protected $vencimento;

    protected function dvBarra($numero)
    {
        $resto2 = $this->modulo_11($numero, 9, 1);
        if ($resto2 == 0 || $resto2 == 1 || $resto2 == 10) {
            $dv = 1;
        } else {
            $dv = 11 - $resto2;
        }
        return $dv;
    }

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

    protected function modulo_11($num, $base = 9, $r = 0)
    {
        $soma = 0;
        $fator = 2;

        /* Separacao dos numeros */
        for ($i = strlen($num); $i > 0; $i--) {
            $numeros[$i] = substr($num, $i - 1, 1);
            $parcial[$i] = $numeros[$i] * $fator;
            $soma += $parcial[$i];
            if ($fator == $base) {
                // restaura fator de multiplicacao para 2
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

    protected function formata_numero($numero, $loop, $insert, $tipo = "geral")
    {
        if ($tipo == "geral") {
            $numero = str_replace(",", "", $numero);
            while (strlen($numero) < $loop) {
                $numero = $insert . $numero;
            }
        }
        if ($tipo == "valor") {
            $numero = str_replace(",", "", $numero);
            while (strlen($numero) < $loop) {
                $numero = $insert . $numero;
            }
        }
        if ($tipo == "convenio") {
            while (strlen($numero) < $loop) {
                $numero = $numero . $insert;
            }
        }
        return $numero;
    }

    protected function esquerda($entra, $comp)
    {
        return substr($entra, 0, $comp);
    }

    protected function direita($entra, $comp)
    {
        return substr($entra, strlen($entra) - $comp, $comp);
    }

    protected function fatorVencimento(\DateTime $date)
    {
        $datetime1 = new \DateTime('1997-10-07');
        $interval = $datetime1->diff($date);
        return abs($interval->format('%R%a'));
    }

    protected function geraCodigoBanco($numero)
    {
        $parte1 = substr($numero, 0, 3);
        $parte2 = $this->modulo_11($parte1);
        return $parte1 . "-" . $parte2;
    }

    protected function codificar($codigo)
    {
        $cbinicio = "NNNN";
        $cbfinal = "WNN";
        $cbresult = '';
        $cbnumeros = array("NNWWN", "WNNNW", "NWNNW", "WWNNN", "NNWNW", "WNWNN", "NWWNN", "NNNWW", "WNNWN", "NWNWN");
        if (is_numeric($codigo) & (!(strlen($codigo) & 1))) {
            for ($i = 0; $i < strlen($codigo); $i = $i + 2) {
                $cbvar1 = $cbnumeros[$codigo[$i]];
                $cbvar2 = $cbnumeros[$codigo[$i + 1]];
                for ($j = 0; $j <= 4; $j++) {
                    $cbresult .= $cbvar1[$j] . $cbvar2[$j];
                }
            }
            return $cbinicio . $cbresult . $cbfinal;
        } else return '';
    }

    public function getCodigoBarrasBase64($codigo = null, $altura = 50, $espmin = 1.7)
    {
        if (is_null($codigo)) {
            $codigo = $this->getCodigoBarras();
        }
        $mapaI25 = $this->codificar($codigo);
        if (!extension_loaded('gd')) {
            //dl('php_gd2.dll');
        }
        $espmin--;
        if ($espmin < 0) {
            $espmin = 0;
        }
        if ($altura < 5) {
            $altura = 5;
        }
        $largura = (strlen($mapaI25) / 5 * ((($espmin + 1) * 3) + (($espmin + 3) * 2))) + 20;
        $im = imagecreate($largura, $altura);
        imagecolorallocate($im, 255, 255, 255);
        $spH = 10;
        for ($k = 0; $k < strlen($mapaI25); $k++) {
            if (!($k & 1)) {
                $corbarra = imagecolorallocate($im, 0, 0, 0);
            } else {
                $corbarra = imagecolorallocate($im, 255, 255, 255);
            }
            if ($mapaI25[$k] == 'N') {
                imagefilledrectangle($im, $spH, $altura - 3, $spH + $espmin, 2, $corbarra);
                $spH = $spH + $espmin + 1;
            } else {
                imagefilledrectangle($im, $spH, $altura - 3, $spH + $espmin + 2, 2, $corbarra);
                $spH = $spH + $espmin + 3;
            }
        }

        ob_start();
        imagepng($im);
        $buffer = ob_get_clean();
        ob_end_clean();
        imagedestroy($im);
        return base64_encode($buffer);
    }

    public function getLinhaDigitavelBase64($codigo = null, $altura = 35, $largura = 450, $fontsize = 11, $font = 'arialbd.ttf')
    {
        if (is_null($codigo)) {
            $codigo = $this->getLinhaDigitavel();
        }

        $font = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Font' . DIRECTORY_SEPARATOR . $font;

        $im = imagecreate($largura, $altura);
        imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        $dimensions = imagettfbbox($fontsize, 0, $font, $codigo);
        $textWidth = abs($dimensions[4] - $dimensions[0]);
        $textHeight = abs($dimensions[7]) + abs($dimensions[1]);
        $x = imagesx($im) - $textWidth;
        $y = ((imagesy($im) / 2) - ($textHeight / 2)) + abs($dimensions[7]);

        imagettftext($im, $fontsize, 0, $x, $y, $black, $font, $codigo);
        ob_start();
        imagepng($im);
        $buffer = ob_get_clean();
        ob_end_clean();
        imagedestroy($im);
        return base64_encode($buffer);

    }

    protected function vencimentoJuliano(\DateTime $date)
    {
        $dias = (int)$date->format('z') + 1;
        $year = $date->format('y');
        return str_pad($dias, 3, '0', STR_PAD_LEFT) . substr($year, -1);
    }

    public function __destruct()
    {
    }

}