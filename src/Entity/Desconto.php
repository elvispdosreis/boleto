<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 15/12/2017
 * Time: 09:20
 */

namespace Boleto\Entity;


class Desconto
{

    const Valor = 1;
    const Percentual = 2;

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
     * Desconto constructor.
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
     * @return Desconto
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
     * @return Desconto
     */
    public function setValor(float $valor): Desconto
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
     * @return Desconto
     */
    public function setData(\DateTime $data): Desconto
    {
        $this->data = $data;
        return $this;
    }

}