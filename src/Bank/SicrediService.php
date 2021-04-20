<?php

namespace Boleto\Bank;

use Boleto\Entity\Beneficiario;
use Boleto\Entity\Multa;
use Boleto\Exception\InvalidArgumentException;
use Boleto\Entity\Pagador;
use Boleto\Entity\Juros;
use Boleto\Entity\Desconto;
use Boleto\Helper\Helper;
use Cache\Adapter\Apcu\ApcuCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SicrediService implements InterfaceBank
{
    private $agencia;
    private $posto;
    private $cedente;
    private $codigoSacadorAvalista;
    private $seuNumero;
    private $vencimento;
    private $valor;


    /**
     * @var Pagador
     */
    private $pagador;

    /**
     * @var Beneficiario
     */
    private $beneficiario;

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

    private $descontoAntecipado;
    private $mensagem;
    private $codigoMensagem;
    private $informativo;

    private $cache;

    private $carteira;
    private $nossoNumero;
    private $codigobarras;
    private $linhadigitavel;
    private $token;
    private $client;

    function __construct($agencia = null, $posto = null, $cedente = null, $codigoSacadorAvalista = null, $seuNumero = null,
                         \DateTime $vencimento = null, $valor = null, $juros = null, $descontoAntecipado = null, $mensagem = null,
                         $codigoMensagem = null, $informativo = null, $pagador = null, $desconto = [], $clientId = 'aaa', $secredId = null)
    {
        $this->cache = new ApcuCachePool();
        $this->agencia = $agencia;
        $this->posto = $posto;
        $this->cedente = $cedente;
        $this->codigoSacadorAvalista = $codigoSacadorAvalista;
        $this->seuNumero = $seuNumero;
        $this->vencimento = $vencimento;
        $this->valor = $valor;
        $this->juros = $juros;
        $this->descontoAntecipado = $descontoAntecipado;
        $this->mensagem = $mensagem;
        $this->codigoMensagem = $codigoMensagem;
        $this->informativo = $informativo;
        $this->pagador = $pagador;
        $this->desconto = $desconto;
        $this->clientId = $clientId;
        $this->secretId = $secredId;
        $this->client = new Client([
            'base_uri' => 'https://cobrancaonline.sicredi.com.br',
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json; charset=utf-8'
            ],
            'verify' => false
        ]);
    }

    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;
        return $this;
    }

    private function getAgencia()
    {
        if (is_null($this->agencia)) {
            throw new \InvalidArgumentException('Agência inválida.');
        }
        return $this->agencia;
    }

    private function getPosto()
    {
        if (is_null($this->posto)) {
            throw new \InvalidArgumentException('Código do Posto inválido.');
        }
        return $this->posto;
    }

    public function setPosto($posto)
    {
        $this->posto = $posto;
        return $this;
    }

    private function getCedente()
    {
        if (is_null($this->cedente)) {
            throw new \InvalidArgumentException('Código do cedente inválido.');
        }
        return $this->cedente;
    }

    public function setCedente($cedente)
    {
        $this->cedente = $cedente;
        return $this;
    }

    public function getCodigoSacadorAvalista()
    {
        return $this->codigoSacadorAvalista;
    }

    public function setCodigoSacadorAvalista($codigoSacadorAvalista)
    {
        $this->codigoSacadorAvalista = $codigoSacadorAvalista;
        return $this;
    }

    public function getSeuNumero()
    {
        return $this->seuNumero;
    }

    public function setSeuNumero($seuNumero)
    {
        $this->seuNumero = $seuNumero;
        return $this;
    }

    public function getDesconto(): Desconto
    {
        return $this->desconto;
    }

    public function setDesconto(Desconto $desconto): SicrediService
    {
        array_push($this->desconto, $desconto);
        return $this;
    }

    public function getJuros(): Juros
    {
        return $this->juros;
    }

    public function setJuros(Juros $juros): SicrediService
    {
        $this->juros = $juros;
        return $this;
    }

    public function getMensagem()
    {
        return $this->mensagem;
    }

    public function setMensagem($mensagem)
    {
        $this->mensagem = $mensagem;
        return $this;
    }

    public function getCodigoMensagem()
    {
        return $this->codigoMensagem;
    }

    public function setCodigoMensagem($codigoMensagem)
    {
        $this->codigoMensagem = $codigoMensagem;
        return $this;
    }

    public function getInformativo()
    {
        return $this->informativo;
    }

    public function setInformativo($informativo)
    {
        $this->informativo = $informativo;
        return $this;
    }

    public function setPagador(Pagador $pagador = null)
    {
        $this->pagador = $pagador;
        return $this;
    }

    public function getVencimento()
    {
        if (is_null($this->vencimento)) {
            throw new \InvalidArgumentException('Data de Vencimento inválida.');
        }
        return $this->vencimento;
    }

    public function setVencimento(\DateTime $date)
    {
        $this->vencimento = $date;
        return $this;
    }

    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    public function getValor()
    {
        if (is_null($this->valor)) {
            throw new \InvalidArgumentException('Valor inválido.');
        }
        return $this->valor;
    }

    private function setLinhadigitavel($linhadigitavel)
    {
        $this->linhadigitavel = $linhadigitavel;
    }

    public function getLinhaDigitavel()
    {
        return $this->linhadigitavel;
    }

    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    public function getCarteira()
    {
        if (is_null($this->carteira)) {
            throw new \InvalidArgumentException('Carteira inválido.');
        }
        return str_pad($this->carteira, 2, "0", STR_PAD_LEFT);
    }

    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;
        return $this;
    }

    public function getNossoNumero()
    {
        if (is_null($this->nossoNumero)) {
            throw new \InvalidArgumentException('Nosso Numero inválido.');
        }
        return $this->nossoNumero;
    }

    private function setCodigobarras($codigobarras)
    {
        $this->codigobarras = $codigobarras;
    }

    public function getCodigoBarras()
    {
        return $this->codigobarras;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    private function getToken()
    {
        try {
            $key = sha1('boleto-sicredi' . $this->token . $this->agencia . $this->getCedente());
            $item = $this->cache->getItem($key);
            if (!$item->isHit()) {
                $res = $this->client->request('POST', '/sicredi-cobranca-ws-ecomm-api/ecomm/v1/boleto/autenticacao', [
                    'headers' => [
                        'token' => $this->token
                    ]
                ]);

                if ($res->getStatusCode() === 200) {
                    $arr = json_decode($res->getBody()->getContents());
                    $now = new \DateTime();

                    $expires_in = new \DateTime($arr->dataExpiracao);
                    $item->set($arr->chaveTransacao);
                    $item->expiresAfter($expires_in->getTimestamp() - $now->getTimestamp());
                    $this->cache->saveDeferred($item);
                    return $item->get();
                }
            }
            return $item->get();
        } catch (RequestException $e) {
            echo $e->getMessage() . PHP_EOL;
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;;
        }
    }

    public function send()
    {
        try {
            $token = $this->getToken();
            $arr = new \stdClass();
            $arr->agencia = $this->getAgencia();
            $arr->posto = $this->getPosto();
            $arr->cedente = $this->getCedente();
            $arr->tipoPessoa = $this->pagador->getTipoDocumento() === 'CPF' ? '1' : '2';
            $arr->cpfCnpj = Helper::number($this->pagador->getDocumento());
            $arr->nome = substr(Helper::ascii($this->pagador->getNome()), 0, 40);
            $arr->cep = $this->pagador->getCep();
            $arr->especieDocumento = 'K'; // OUTROS - OS
            $arr->codigoSacadorAvalista = $this->getCodigoSacadorAvalista();
            $arr->seuNumero = $this->getSeuNumero();
            $arr->nossoNumero = $this->getNossoNumero();
            $arr->dataVencimento = $this->getVencimento()->format('d/m/Y');
            $arr->valor = $this->getValor();

            if (count($this->desconto) > 0) {
                if (count($this->desconto) > 3) {
                    throw new \InvalidArgumentException('Quantidade desconto informado maior que 3.');
                }

                foreach ($this->desconto as $x => $desconto) {
                    if ($x > 0) {
                        if ($this->desconto[$x]->getTipo() !== $this->desconto[$x - 1]->getTipo()) {
                            throw new \InvalidArgumentException('Código do tipo de desconto ' . ($x + 1) . ' está diferente do tipo de desconto 1.');
                        }
                    }
                    if ($desconto->getTipo() === $desconto::Valor) {
                        $arr->tipoDesconto = 'A';
                        $arr->{'valorDesconto' . ($x + 1)} = $desconto->getValor();
                        $arr->{'dataDesconto' . ($x + 1)} = $desconto->getData()->format('d/m/Y');
                    } elseif ($desconto->getTipo() === $desconto::Percentual) {
                        $arr->tipoDesconto = 'B';
                        $arr->{'valorDesconto' . ($x + 1)} = $desconto->getValor();
                        $arr->{'dataDesconto' . ($x + 1)} = $desconto->getData()->format('d/m/Y');
                    } else {
                        throw new \InvalidArgumentException('Código do tipo de desconto inválido.');
                    }
                }
            } else {
                $arr->tipoDesconto = 'A';
                $arr->valorDesconto1 = 0;
                $arr->dataDesconto1 = '';
                $arr->valorDesconto2 = 0;
                $arr->dataDesconto2 = '';
                $arr->valorDesconto3 = 0;
                $arr->dataDesconto3 = '';
            }

            if($this->juros) {
                $arr->tipoJuros = 'A';
                $arr->juros = $this->juros->getValor();
            }



            $arr->descontoAntecipado = 0;
            $arr->mensagem = substr(Helper::ascii($this->getMensagem()), 0, 300);
            $arr->codigoMensagem = $this->getCodigoMensagem();
            $arr->endereco = substr(Helper::ascii($this->pagador->getLogradouro()), 0, 40);
            $arr->cidade = substr(Helper::ascii($this->pagador->getCidade()), 0, 25);
            $arr->uf = substr(Helper::ascii($this->pagador->getUf()), 0, 2);
            $arr->telefone = $this->pagador->getTelefone();
            $arr->informativo = substr(Helper::ascii($this->getInformativo()), 0, 80);


            $res = $this->client->request('POST', '/sicredi-cobranca-ws-ecomm-api/ecomm/v1/boleto/emissao', [
                'json' => $arr,
                'headers' => ['token' => $token]
            ]);

            if ($res->getStatusCode() === 201) {
                $retorno = json_decode($res->getBody()->getContents());

                $this->setLinhadigitavel($retorno->linhaDigitavel);
                $this->setCodigobarras($retorno->codigoBarra);
            }
        } catch (RequestException $e) {

            if($e->hasResponse()) {
                $error = json_decode($e->getResponse()->getBody()->getContents());
                throw new \Exception("{$error->codigo} - {$error->mensagem}", $e->getCode());
            } else {
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
