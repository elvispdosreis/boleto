<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:05
 */

namespace Boleto\Bank;

use Boleto\Entity\Desconto;
use Boleto\Helper\Helper;
use Boleto\Entity\Juros;
use Boleto\Entity\Multa;
use Boleto\Entity\Pagador;
use Boleto\Exception\InvalidArgumentException;
use Cache\Adapter\Apcu\ApcuCachePool;
use GuzzleHttp\Client;
use DateTime, Exception, SoapClient, SoapFault, SimpleXMLElement;

class BrasilService implements InterfaceBank
{


    /**
     * @var Datetime
     */
    private $vencimento, $emissao;
    private $valor;
    private $convenio;
    private $variacaocarteira;
    private $nossonumero;
    private $carteira;
    private $codigobarras;
    private $linhadigitavel;

    /**
     * @var Pagador
     */
    private $pagador;

    /**
     * @var Juros
     */
    private $juros;

    /**
     * @var Multa
     */
    private $multa;

    /**
     * @var Desconto[]
     */
    private $desconto = [];

    private $clientId;
    private $secretId;
    private $cache;

    private $sandbox = false;


    /**
     * BrasilService constructor.
     * @param DateTime|null $vencimento
     * @param null $valor
     * @param null $nossonumero
     * @param null $carteira
     * @param null $convenio
     * @param null $variacaocarteira
     * @param Pagador|null $pagador
     * @param null $clientId
     * @param null $secredId
     * @throws Exception
     */
    public function __construct(Datetime $vencimento = null, $valor = null, $nossonumero = null, $carteira = null, $convenio = null, $variacaocarteira = null, Pagador $pagador = null, $clientId = null, $secredId = null)
    {
        $this->cache = new ApcuCachePool();

        $this->emissao = new Datetime();
        $this->vencimento = $vencimento;
        $this->valor = $valor;
        $this->nossonumero = $nossonumero;
        $this->carteira = $carteira;
        $this->convenio = $convenio;
        $this->variacaocarteira = $variacaocarteira;
        $this->pagador = $pagador;
        $this->clientId = $clientId;
        $this->secretId = $secredId;
    }

    /**
     * @param Datetime $date
     * @return BrasilService
     */
    public function setEmissao(Datetime $date)
    {
        $this->emissao = $date;
        return $this;
    }

    /**
     * @param Datetime $date
     * @return BrasilService
     */
    public function setVencimento(Datetime $date)
    {
        $this->vencimento = $date;
        return $this;
    }

    /**
     * @param double $valor
     * @return BrasilService
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @param int $nossonumero
     * @return BrasilService
     */
    public function setNossoNumero($nossonumero)
    {
        $this->nossonumero = $nossonumero;
        return $this;
    }

    /**
     * @param int $convenio
     * @return BrasilService
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    /**
     * @param int $variacaocarteira
     * @return BrasilService
     */
    public function setVariacaoCarteira($variacaocarteira)
    {
        $this->variacaocarteira = $variacaocarteira;
        return $this;
    }

    /**
     * @param int $carteira
     * @return BrasilService
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    /**
     * @param Pagador $pagador
     * @return BrasilService
     */
    public function setPagador(Pagador $pagador = null)
    {
        $this->pagador = $pagador;
        return $this;
    }

    /**
     * @param string $clientId
     * @return BrasilService
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @param string $clientId
     * @return BrasilService
     */
    public function setSecretId($secretId)
    {
        $this->secretId = $secretId;
        return $this;
    }

    /**
     * @return string
     */
    private function getClientId()
    {
        if (is_null($this->clientId)) {
            throw new \InvalidArgumentException('O parâmetro clientId nulo.');
        }
        return $this->clientId;
    }

    /**
     * @return string
     */
    private function getSecretId()
    {
        if (is_null($this->clientId)) {
            throw new \InvalidArgumentException('O parâmetro secretId nulo.');
        }
        return $this->secretId;
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
     * @param Datetime
     */
    public function getEmissao()
    {
        if (is_null($this->emissao)) {
            throw new \InvalidArgumentException('Data Emissäo inválido.');
        }
        return $this->emissao;
    }

    /**
     * @param Datetime
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
        return $this->valor;
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

    /**
     * @return string
     */
    private function getVariacaCarteira()
    {
        if (is_null($this->variacaocarteira)) {
            throw new \InvalidArgumentException('Variação Carteira inválido.');
        }
        return $this->variacaocarteira;
    }

    /**
     * @return Juros
     */
    public function getJuros(): Juros
    {
        return $this->juros;
    }

    /**
     * @param Juros $juros
     * @return BrasilService
     */
    public function setJuros(Juros $juros): BrasilService
    {
        $this->juros = $juros;
        return $this;
    }

    /**
     * @return Multa
     */
    public function getMulta(): Multa
    {
        return $this->multa;
    }

    /**
     * @param Multa $multa
     * @return BrasilService
     */
    public function setMulta(Multa $multa): BrasilService
    {
        $this->multa = $multa;
        return $this;
    }

    /**
     * @return Desconto[]
     */
    public function getDesconto(): Desconto
    {
        return $this->desconto;
    }

    /**
     * @param Desconto $desconto
     * @return BrasilService
     */
    public function setDesconto(Desconto $desconto): BrasilService
    {
        array_push($this->desconto, $desconto);
        return $this;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     * @return BrasilService
     */
    public function setSandbox(bool $sandbox): BrasilService
    {
        $this->sandbox = $sandbox;
        return $this;
    }


    public function send()
    {

        try {

            $token = $this->getToken();

            $httpHeaders = [
                'http' => [
                    'protocol_version' => 1.1,
                    'header' => "Authorization: Bearer " . $token . "\r\n" . "Cache-Control: no-cache"
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $context = stream_context_create($httpHeaders);

            if ($this->isSandbox()) {
                /* Problema de SSL no Endpoint
                $endpoint = 'https://cobranca.homologa.bb.com.br:7101/Processos/Ws/RegistroCobrancaService.serviceagent?wsdl';
                */
                $endpoint = dirname(__FILE__) . '/../XSD/Banco do Brasil/RegistroCobrancaServiceHomologacao.xml';
            } else {
                /* Problema de SSL no Endpoint
                $endpoint = 'https://cobranca.bb.com.br:7101/Processos/Ws/RegistroCobrancaService.serviceagent?wsdl';
                */
                $endpoint = dirname(__FILE__) . '/../XSD/Banco do Brasil/RegistroCobrancaService.xml';
            }

            $client = new SoapClient($endpoint,
                [
                    'trace' => TRUE,
                    'exceptions' => TRUE,
                    'encoding' => 'UTF-8',
                    'compression' => \SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_GZIP,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'connection_timeout' => 30,
                    'stream_context' => $context
                ]
            );

            $titulo = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><Message/>');

            $titulo->addChild('numeroConvenio', $this->getConvenio());
            $titulo->addChild('numeroCarteira', $this->getCarteira());
            $titulo->addChild('numeroVariacaoCarteira', $this->getVariacaCarteira());

            $titulo->addChild('codigoModalidadeTitulo', 1);
            $titulo->addChild('dataEmissaoTitulo', $this->getEmissao()->format('d.m.Y'));
            $titulo->addChild('dataVencimentoTitulo', $this->getVencimento()->format('d.m.Y'));
            $titulo->addChild('valorOriginalTitulo', $this->getValor());


            if (count($this->desconto) > 0) {
                if (count($this->desconto) > 1) {
                    throw new \InvalidArgumentException('Quantidade desconto informado maior que 1.');
                }
                foreach ($this->desconto as $desconto) {
                    if ($desconto->getTipo() === $desconto::Valor) {
                        $titulo->addChild('codigoTipoDesconto', '1');
                        $titulo->addChild('dataDescontoTitulo', $desconto->getData()->format('d.m.Y'));
                        $titulo->addChild('valorDescontoTitulo', $desconto->getValor());
                    } elseif ($desconto->getTipo() === $desconto::Percentual) {
                        $titulo = $titulo->addChild('codigoTipoDesconto', '2');
                        $titulo->addChild('dataDescontoTitulo', $desconto->getData()->format('d.m.Y'));
                        $titulo->addChild('percentualDescontoTitulo', $desconto->getValor());
                    } else {
                        throw new \InvalidArgumentException('Código do tipo de desconto inválido.');
                    }
                }
            } else {
                $titulo->addChild('codigoTipoDesconto', '');
            }


            $multa = $this->multa;
            if (!is_null($this->multa)) {
                $titulo->addChild('codigoTipoMulta', 2);
                $titulo->addChild('percentualMultaTitulo', $multa->getPercentual());
                $titulo->addChild('dataMultaTitulo', $multa->getData()->format('d.m.Y'));
            } else {
                $titulo->addChild('codigoTipoMulta', 0);
            }


            $juros = $this->juros;
            if (!is_null($this->juros)) {
                if ($juros->getTipo() === $this->juros::Isento) {
                    $titulo->addChild('codigoTipoJuroMora', 0);
                } elseif ($juros->getTipo() === $this->juros::Diario) {
                    $titulo->addChild('codigoTipoJuroMora', 1);
                    $titulo->addChild('valorJuroMoraTitulo', $juros->getValor());
                } elseif ($juros->getTipo() === $this->juros::Mensal) {
                    $titulo->addChild('codigoTipoJuroMora', 2);
                    $titulo->addChild('percentualJuroMoraTitulo', $juros->getValor());
                } else {
                    throw new \InvalidArgumentException('Código do tipo de juros inválido.');
                }
            } else {
                $titulo->addChild('codigoTipoJuroMora', 0);
            }

            $titulo->addChild('codigoAceiteTitulo', 'N');
            $titulo->addChild('codigoTipoTitulo', 99);

            $titulo->addChild('indicadorPermissaoRecebimentoParcial', 'N');
            $nossonumero = '000' . str_pad($this->getConvenio(), 7, '0') . str_pad($this->getNossoNumero(), 10, '0', STR_PAD_LEFT);
            $titulo->addChild('textoNumeroTituloCliente', $nossonumero);

            $titulo->addChild('codigoTipoInscricaoPagador', $this->pagador->getTipoDocumento() === 'CPF' ? 1 : 2);
            $titulo->addChild('numeroInscricaoPagador', $this->pagador->getDocumento());
            $titulo->addChild('nomePagador', substr(Helper::ascii($this->pagador->getNome()), 0, 60));
            $titulo->addChild('textoEnderecoPagador', substr(Helper::ascii($this->pagador->getLogradouro() . ' ' . $this->pagador->getNumero()), 0, 60));
            $titulo->addChild('numeroCepPagador', substr(Helper::number($this->pagador->getCep()), 0, 8));
            $titulo->addChild('nomeMunicipioPagador', substr(Helper::ascii($this->pagador->getCidade()), 0, 20));
            $titulo->addChild('nomeBairroPagador', substr(Helper::ascii($this->pagador->getBairro()), 0, 20));
            $titulo->addChild('siglaUfPagador', $this->pagador->getUf());
            $titulo->addChild('textoNumeroTelefonePagador', $this->pagador->getTelefone());

            $titulo->addChild('codigoChaveUsuario', 'J1234567');
            $titulo->addChild('codigoTipoCanalSolicitacao', 5);

            $result = $client->__soapCall("RegistroTituloCobranca", [$titulo]);

            if ($result->codigoRetornoPrograma !== 0) {
                throw new InvalidArgumentException($result->nomeProgramaErro, trim($result->textoMensagemErro));
            }

            $this->setCodigobarras($result->codigoBarraNumerico);
            $this->setLinhadigitavel($result->linhaDigitavel);

        } catch (SoapFault $sf) {
            throw new Exception($sf->faultstring, 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500, $e);
        }

    }

    private function getToken()
    {
        try {

            $key = sha1('boleto-bb' . $this->convenio);

            if ($this->isSandbox()) {
                $endpoint = 'https://oauth.hm.bb.com.br/oauth/token';
            } else {
                $endpoint = 'https://oauth.bb.com.br/oauth/token';
            }

            $item = $this->cache->getItem($key);
            if (!$item->isHit()) {
                $client = new Client(['auth' => [$this->getClientId(), $this->getSecretId()]]);
                $res = $client->request('POST', $endpoint, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Cache-Control' => 'no-cache'
                    ],
                    'body' => 'grant_type=client_credentials&scope=cobranca.registro-boletos'
                ]);

                if ($res->getStatusCode() === 200) {
                    $json = $res->getBody()->getContents();
                    $arr = \GuzzleHttp\json_decode($json);

                    $item->set($arr->access_token);
                    $item->expiresAfter($arr->expires_in);
                    $this->cache->saveDeferred($item);
                    return $item->get();
                }

                return null;

            }
            return $item->get();

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
 
