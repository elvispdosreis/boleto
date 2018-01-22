<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:05
 */

namespace Boleto\Bank;


use Boleto\Entity\Pagador;
use Cache\Adapter\Apcu\ApcuCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use function GuzzleHttp\Psr7\str;
use Meng\AsyncSoap\Guzzle\Factory;

class BradescoService implements InterfaceBank
{


    /**
     * @var \DateTime
     */
    private $vencimento;
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

    private $clientId;
    private $secretId;
    private $cache;


    /**
     * BrasilService constructor.
     * @param string $vencimento
     * @param string $valor
     * @param string $convenio
     * @param string $variacaocarteira
     * @param string $nossonumero
     * @param string $carteira
     */
    public function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero = null, $carteira = null, $convenio = null, $variacaocarteira = null, Pagador $pagador = null, $clientId = null, $secredId = null)
    {
        $this->cache = new ApcuCachePool();

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
     * @param \DateTime $date
     * @return BrasilService
     */
    public function setVencimento(\DateTime $date)
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
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $context = stream_context_create($httpHeaders);

            $client = new \SoapClient(dirname(__FILE__) . '/../XSD/RegistroCobrancaService.xml',
                [
                    'trace' => TRUE,
                    'exceptions' => TRUE,
                    'encoding' => 'UTF-8',
                    'compression' => \SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_GZIP,
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'connection_timeout' => 15,
                    'stream_context' => $context
                ]
            );


            $arr = new \stdClass();
            $arr->nuCPFCNPJ = '123456789';
            $arr->filialCPFCNPJ = '0001';
            $arr->ctrlCPFCNPJ = '39';
            $arr->cdTipoAcesso = '2';
            $arr->clubBanco = '0';
            $arr->cdTipoContrato = '0';
            $arr->nuSequenciaContrato = '0';
            $arr->idProduto = '09';
            $arr->nuNegociacao = '123400000001234567';
            $arr->cdBanco = '237';
            $arr->eNuSequenciaContrato = '0';
            $arr->tpRegistro = '1';
            $arr->cdProduto = '0';
            $arr->nuTitulo = '0';
            $arr->nuCliente = '123456';
            $arr->dtEmissaoTitulo = $this->getVencimento()->format('d.m.Y');
            $arr->dtVencimentoTitulo = $this->getVencimento()->format('d.m.Y');
            $arr->tpVencimento = '0';
            $arr->vlNominalTitulo = $this->getValor();
            $arr->cdEspecieTitulo = '04';
            $arr->tpProtestoAutomaticoNegativacao = '0';
            $arr->prazoProtestoAutomaticoNegativacao = '0';
            $arr->controleParticipante = '';
            $arr->cdPagamentoParcial = '';
            $arr->qtdePagamentoParcial = '0';
            $arr->percentualJuros = '0';
            $arr->vlJuros = '0';
            $arr->qtdeDiasJuros = '0';
            $arr->percentualMulta = '0';
            $arr->vlMulta = '0';
            $arr->qtdeDiasMulta = '0';
            $arr->percentualDesconto1 = '0';
            $arr->vlDesconto1 = '0';
            $arr->dataLimiteDesconto1 = '';
            $arr->percentualDesconto2 = '0';
            $arr->vlDesconto2 = '0';
            $arr->dataLimiteDesconto2 = '';
            $arr->percentualDesconto3 = '0';
            $arr->vlDesconto3 = '0';
            $arr->dataLimiteDesconto3 = '';
            $arr->prazoBonificacao = '0';
            $arr->percentualBonificacao = '0';
            $arr->vlBonificacao = '0';
            $arr->dtLimiteBonificacao = '';
            $arr->vlAbatimento = '0';
            $arr->vlIOF = '0';
            $arr->nomePagador = $this->pagador->getNome();
            $arr->logradouroPagador = $this->pagador->getLogradouro();
            $arr->nuLogradouroPagador = $this->pagador->getNumero();
            $arr->complementoLogradouroPagador = $this->pagador->getComplemento();
            $arr->cepPagador = '12345';
            $arr->complementoCepPagador = '500';
            $arr->bairroPagador = $this->pagador->getBairro();
            $arr->municipioPagador = $this->pagador->getCidade();
            $arr->ufPagador = 'SP';
            $arr->cdIndCpfcnpjPagador = '1';
            $arr->nuCpfcnpjPagador = $this->pagador->getDocumento();
            $arr->endEletronicoPagador = $this->pagador->getEmail();
            $arr->nomeSacadorAvalista = '';
            $arr->logradouroSacadorAvalista = '';
            $arr->nuLogradouroSacadorAvalista = '0';
            $arr->complementoLogradouroSacadorAvalista = '';
            $arr->cepSacadorAvalista = '0';
            $arr->complementoCepSacadorAvalista = '0';
            $arr->bairroSacadorAvalista = '';
            $arr->municipioSacadorAvalista = '';
            $arr->ufSacadorAvalista = '';
            $arr->cdIndCpfcnpjSacadorAvalista = '0';
            $arr->nuCpfcnpjSacadorAvalista = '0';
            $arr->endEletronicoSacadorAvalista = '';


            if ($result->codigoRetornoPrograma !== 0) {
                throw new \Exception(trim($result->textoMensagemErro));
            }

            $this->setCodigobarras($result->codigoBarraNumerico);
            $this->setLinhadigitavel($result->linhaDigitavel);

        } catch (\SoapFault $sf) {
            throw new \Exception($sf->faultstring, 500);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }

    }

    private function getToken()
    {
        try {

            $key = sha1('boleto-bb' . $this->convenio);

            $item = $this->cache->getItem($key);
            if (!$item->isHit()) {
                $client = new Client(['auth' => [$this->getClientId(), $this->getSecretId()]]);
                $res = $client->request('POST', 'https://oauth.hm.bb.com.br/oauth/token', [
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

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}