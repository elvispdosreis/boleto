<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:05
 */

namespace Boleto\Bank;


use Boleto\Helper\Helper;
use Boleto\Entity\Beneficiario;
use Boleto\Entity\Pagador;
use Boleto\Exception\InvalidArgumentException;
use Boleto\Service\CaixaSoapCliente;
use Zend\Soap\Client;


class CaixaService implements InterfaceBank
{


    /**
     * @var \DateTime
     */
    private $vencimento, $emissao;
    private $valor;
    private $convenio;
    private $nossonumero;
    private $carteira;
    private $codigobarras;
    private $linhadigitavel;

    /**
     * @var Pagador
     */
    private $pagador;

    /**
     * @var Beneficiario
     */
    private $beneficiario;


    /**
     * CaixaService constructor.
     * @param string $vencimento
     * @param string $valor
     * @param string $nossonumero
     * @param string $convenio
     * @param Pagador $pagador
     */
    public function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero = null, $convenio = null, Pagador $pagador = null)
    {
        $this->emissao = new \DateTime();
        $this->vencimento = $vencimento;
        $this->valor = $valor;
        $this->nossonumero = $nossonumero;
        $this->convenio = $convenio;
        $this->pagador = $pagador;
    }

    /**
     * @param \DateTime $date
     * @return CaixaService
     */
    public function setEmissao(\DateTime $date)
    {
        $this->emissao = $date;
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return CaixaService
     */
    public function setVencimento(\DateTime $date)
    {
        $this->vencimento = $date;
        return $this;
    }

    /**
     * @param double $valor
     * @return CaixaService
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @param int $nossonumero
     * @return CaixaService
     */
    public function setNossoNumero($nossonumero)
    {
        $this->nossonumero = $nossonumero;
        return $this;
    }

    /**
     * @param int $convenio
     * @return CaixaService
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }


    /**
     * @param Pagador $pagador
     * @return CaixaService
     */
    public function setPagador(Pagador $pagador = null)
    {
        $this->pagador = $pagador;
        return $this;
    }

    /**
     * @param Beneficiario $pagador
     * @return CaixaService
     */
    public function setBeneficiario(Beneficiario $beneficiario = null)
    {
        $this->beneficiario = $beneficiario;
        return $this;
    }

    /**
     * @param string $codigobarras
     */
    private function setCodigobarras($codigobarras)
    {
        $this->codigobarras = $codigobarras;
    }

    /**
     * @param string $linhadigitavel
     */
    private function setLinhadigitavel($linhadigitavel)
    {
        $this->linhadigitavel = $linhadigitavel;
    }

    /**
     * @param \DateTime
     */
    public function getEmissao()
    {
        if (is_null($this->emissao)) {
            throw new \InvalidArgumentException('Data Emissäo inválido.');
        }
        return $this->vencimento;
    }

    /**
     * @param \DateTime
     */
    public function getVencimento()
    {
        if (is_null($this->vencimento)) {
            throw new \InvalidArgumentException('Data Vencimento inválido.');
        }
        return $this->vencimento;
    }

    /**
     * @return int
     */
    public function getCarteira()
    {
        if (is_null($this->carteira)) {
            throw new \InvalidArgumentException('Carteira inválido.');
        }
        return $this->carteira;
    }

    /**
     * @return double
     */
    public function getValor()
    {
        if (is_null($this->valor)) {
            throw new \InvalidArgumentException('Valor inválido.');
        }
        return Helper::numberFormat($this->valor);
    }

    /**
     * @return string
     */
    public function getNossoNumero()
    {
        if (is_null($this->nossonumero)) {
            throw new \InvalidArgumentException('Nosso Numero inválido.');
        }
        return $this->nossonumero;
    }

    /**
     * @return string
     */
    public function getLinhaDigitavel()
    {
        return $this->linhadigitavel;
    }

    /**
     * @return string
     */
    public function getCodigoBarras()
    {
        return $this->codigobarras;
    }

    /**
     * @return string
     */
    private function getConvenio()
    {
        if (is_null($this->convenio)) {
            throw new \InvalidArgumentException('Convênio inválido.');
        }
        return $this->convenio;
    }


    public function send()
    {

        try {

            $client = new CaixaSoapCliente(dirname(__FILE__) . '/../XSD/Caixa/RegistroCobrancaService.wsdl');
            $client->__setLocation('https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo');

            $now = new \DateTime();

            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><SERVICO_ENTRADA/>');

            $header = $xml->addChild('HEADER');
            $header->addChild('VERSAO', '1.0');
            $header->addChild('AUTENTICACAO', $this->getHash());
            $header->addChild('USUARIO_SERVICO', 'SGCBS01D');
            $header->addChild('OPERACAO', 'INCLUI_BOLETO');
            $header->addChild('SISTEMA_ORIGEM', 'SIGCB');
            $header->addChild('DATA_HORA', $now->format('YmdHis'));

            $dados = $xml->addChild('DADOS');
            $incluir = $dados->addChild('INCLUI_BOLETO');
            $incluir->addChild('CODIGO_BENEFICIARIO', $this->getConvenio());

            $titulo = $incluir->addChild('TITULO');
            $titulo->addChild('NOSSO_NUMERO', '14' . Helper::padLeft($this->getNossoNumero(), 15));
            $titulo->addChild('NUMERO_DOCUMENTO', $this->getNossoNumero());
            $titulo->addChild('DATA_VENCIMENTO', $this->getEmissao()->format('Y-m-d'));
            $titulo->addChild('VALOR', $this->getValor());
            $titulo->addChild('TIPO_ESPECIE', 99);
            $titulo->addChild('FLAG_ACEITE', 'S');
            $titulo->addChild('DATA_EMISSAO', $this->getEmissao()->format('Y-m-d'));

            $juros = $titulo->addChild('JUROS_MORA');
            $juros->addChild('TIPO', 'ISENTO');
            //$juros->addChild('DATA', 0);
            $juros->addChild('PERCENTUAL', 0);

            $titulo->addChild('VALOR_ABATIMENTO', 0);

            $pos = $titulo->addChild('POS_VENCIMENTO');
            $pos->addChild('ACAO', 'DEVOLVER');
            $pos->addChild('NUMERO_DIAS', '0');

            $titulo->addChild('CODIGO_MOEDA', '09');

            $pagador = $titulo->addChild('PAGADOR');
            if ($this->pagador->getTipoDocumento() === 'CPF') {
                $pagador->addChild('CPF', $this->pagador->getDocumento());
                $pagador->addChild('NOME', $this->pagador->getNome());
            } else {
                $pagador->addChild('CNPJ', $this->pagador->getDocumento());
                $pagador->addChild('RAZAO_SOCIAL', $this->pagador->getNome());
            }

            $endereco = $pagador->addChild('ENDERECO');
            $endereco->addChild('LOGRADOURO', $this->pagador->getLogradouro() . ' ' . $this->pagador->getNumero());
            $endereco->addChild('BAIRRO', $this->pagador->getBairro());
            $endereco->addChild('CIDADE', $this->pagador->getCidade());
            $endereco->addChild('UF', $this->pagador->getUf());
            $endereco->addChild('CEP', Helper::number($this->pagador->getCep()));


            $arr = json_decode(json_encode((array)$xml), 1);

            $result = $client->__soapCall("INCLUI_BOLETO", [$arr]);

            if ($result->DADOS->CONTROLE_NEGOCIAL->COD_RETORNO !== "0") {
                throw new InvalidArgumentException($result->DADOS->CONTROLE_NEGOCIAL->MENSAGENS->RETORNO, trim($result->DADOS->CONTROLE_NEGOCIAL->COD_RETORNO));
            }

            $this->setCodigobarras($result->DADOS->INCLUI_BOLETO->CODIGO_BARRAS);
            $this->setLinhadigitavel($result->DADOS->INCLUI_BOLETO->LINHA_DIGITAVEL);

        } catch (\SoapFault $sf) {
            throw new \Exception($sf->faultstring, 500);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500, $e);
        }

    }

    private function getHash()
    {
        try {

            $str = Helper::padLeft($this->getConvenio(), 7)
                . '14' . Helper::padLeft($this->getNossoNumero(), 15)
                . $this->getVencimento()->format('dmY')
                . Helper::padLeft($this->getValor(), 15)
                . Helper::padLeft($this->beneficiario->getDocumento(), 14);

            return base64_encode(hash('sha256', $str, true));

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}