<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 15/12/2017
 * Time: 09:20
 */

namespace Boleto\Entity;


class Juros
{

    const Diario = 1;
    const Mensal = 2;
    const Isento = 3;

    private $tipo;
    /**
     * @var double
     */
    private $valor;
    /**
     * @var \DateTime
     */
    private $data;

    /**
     * Juros constructor.
     * @param $tipo
     * @param float $valor
     * @param \DateTime $data
     */
    public function __construct($tipo, float $valor, \DateTime $data)
    {
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     * @return Juros
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
        return $this;
    }

    /**
     * @return float
     */
    public function getValor(): float
    {
        return $this->valor;
    }

    /**
     * @param float $valor
     * @return Juros
     */
    public function setValor(float $valor): Juros
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getData(): \DateTime
    {
        return $this->data;
    }

    /**
     * @param \DateTime $data
     * @return Juros
     */
    public function setData(\DateTime $data): Juros
    {
        $this->data = $data;
        return $this;
    }

}