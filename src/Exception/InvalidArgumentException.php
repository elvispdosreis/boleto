<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 19/10/2017
 * Time: 15:11
 */


namespace Boleto\Exception;

class InvalidArgumentException extends \Exception
{
    protected $reference;

    public function __construct($reference, $message, $code = 0, \Exception $previous = null) {

        $this->reference = $reference;

        parent::__construct($message, $code, $previous);

    }

    public function getReference(){
        return $this->reference;
    }
}