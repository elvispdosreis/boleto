<?php

/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:51
 */

namespace Boleto\Entity;

class Pagador
{

    private $nome;
    private $documento;
    private $logradouro;
    private $numero;
    private $complemento;
    private $bairro;
    private $cidade;
    private $cep;
    private $uf;

    private $email;
    private $telefone;

    /**
     * Pagador constructor.
     * @param $nome
     * @param $documento
     * @param $logradouro
     * @param $numero
     * @param $complemento
     * @param $bairro
     * @param $cidade
     * @param $cep
     * @param $uf
     * @param $telefone
     * @param $email
     */
    public function __construct($nome = null, $documento = null, $logradouro = null, $numero = null, $complemento = null, $bairro = null, $cidade = null, $uf = null, $cep = null, $telefone = null, $email = null)
    {
        $this->nome = $nome;
        $this->documento = $documento;
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->uf = $uf;
        $this->cep = $cep;
        $this->telefone = $telefone;
        $this->email = $email;
    }


    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     * @return Pagador
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocumento()
    {
        return (string)$this->documento;
    }

    /**
     * @return string
     */
    public function getTipoDocumento()
    {
        $str = preg_replace("/[^0-9]/", "", $this->documento);
        if (strlen($str) === 11) {
            return 'CPF';
        } // Verifica CNPJ
        elseif (strlen($str) === 14) {
            return 'CNPJ';
        }
        else {
            return false;
        }
    }

    /**
     * @param string $documento
     * @return Pagador
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
        return $this;
    }


    /**
     * @return string
     */
    public function getLogradouro()
    {
        return $this->logradouro;
    }

    /**
     * @param string $logradouro
     * @return Pagador
     */
    public function setLogradouro($logradouro)
    {
        $this->logradouro = $logradouro;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param string $numero
     * @return Pagador
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
        return $this;
    }

    /**
     * @return string
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * @param string $complemento
     * @return Pagador
     */
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
        return $this;
    }

    /**
     * @return string
     */
    public function getBairro()
    {
        return $this->bairro;
    }

    /**
     * @param string $bairro
     * @return Pagador
     */
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;
        return $this;
    }

    /**
     * @return string
     */
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * @param string $cidade
     * @return Pagador
     */
    public function setCidade($cidade)
    {
        $this->cidade = $cidade;
        return $this;
    }

    /**
     * @return string
     */
    public function getCep()
    {
        return (string)$this->cep;
    }

    /**
     * @param string $cep
     * @return Pagador
     */
    public function setCep($cep)
    {
        $this->cep = $cep;
        return $this;
    }

    /**
     * @return string
     */
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * @param string $uf
     * @return Pagador
     */
    public function setUf($uf)
    {
        $this->uf = $uf;
        return $this;
    }

    /**
     * @return string
     */
    public function getTelefone()
    {
        return $this->telefone;
    }

    /**
     * @param string $telefone
     * @return Pagador
     */
    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Pagador
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

}