<?php

/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:55
 */

namespace Boleto\Entity;

use Boleto\Helper\Helper;

class Beneficiario extends Pagador
{
    public function getDocumentoRaiz()
    {
        if ($this->getTipoDocumento() === 'CNPJ') {
            $cnpj = Helper::number($this->getDocumento());
            return substr($cnpj, 0, 8);
        }
        return null;

    }

    public function getDocumentoFilial()
    {
        if ($this->getTipoDocumento() === 'CNPJ') {
            $cnpj = Helper::number($this->getDocumento());
            return substr($cnpj, 8, 4);
        }
        return null;
    }

    public function getDocumentoControle()
    {
        if ($this->getTipoDocumento() === 'CNPJ') {
            $cnpj = Helper::number($this->getDocumento());
            return substr($cnpj, 12, 2);
        }
        return null;
    }
}