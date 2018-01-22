<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 15/12/2017
 * Time: 09:20
 */

namespace Boleto\Entity;


class Multa
{

    private $percentual;
    /**
     * @var \DateTime
     */
    private $data;

    /**
     * Juros constructor.
     * @param float $percentual
     * @param \DateTime $data
     */
    public function __construct(float $percentual, \DateTime $data)
    {
        $this->percentual = $percentual;
        $this->data = $data;
    }

    /**
     * @return float
     */
    public function getPercentual(): float
    {
        return $this->percentual;
    }

    /**
     * @param float $valor
     * @return Multa
     */
    public function setPercentual(float $percentual): Multa
    {
        $this->percentual = $percentual;
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
     * @return Multa
     */
    public function setData(\DateTime $data): Multa
    {
        $this->data = $data;
        return $this;
    }
}