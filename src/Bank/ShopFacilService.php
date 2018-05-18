<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:05
 */

namespace Boleto\Bank;


use Boleto\Entity\Beneficiario;
use Boleto\Entity\Desconto;
use Boleto\Entity\Juros;
use Boleto\Entity\Multa;
use Boleto\Entity\Pagador;
use Boleto\Exception\InvalidArgumentException;
use Boleto\Helper\Helper;
use Cache\Adapter\Apcu\ApcuCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

class ShopFacilService implements InterfaceBank
{


    /**
     * @var \DateTime
     */
    private $emissao;
    private $vencimento;
    private $valor;
    private $agencia;
    private $conta;
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
     * @var int
     */
    private $prazodevolucao;

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

    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $secretId;

    /**
     * @var ApcuCachePool
     */
    private $cache;

    /**
     * ShopFacilService constructor.
     * @param \DateTime $vencimento
     * @param string $valor
     * @param string $nossonumero
     * @param string $carteira
     * @param string $agencia
     * @param string $conta
     * @param Pagador $pagador
     * @param Beneficiario $beneficiario
     * @param string $clientId
     * @param string $secredId
     */
    public function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero = null, $carteira = null, $agencia = null, $conta = null, Pagador $pagador = null, Beneficiario $beneficiario = null, $clientId = null, $secredId = null)
    {
        $this->cache = new ApcuCachePool();
        $this->vencimento = $vencimento;
        $this->valor = $valor;
        $this->nossonumero = $nossonumero;
        $this->carteira = $carteira;
        $this->agencia = $agencia;
        $this->conta = $conta;
        $this->pagador = $pagador;
        $this->beneficiario = $beneficiario;
        $this->clientId = $clientId;
        $this->secretId = $secredId;
        $this->prazodevolucao = 29;
    }

    /**
     * @param \DateTime $date
     * @return ShopFacilService
     */
    public function setEmissao(\DateTime $date)
    {
        $this->emissao = $date;
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return ShopFacilService
     */
    public function setVencimento(\DateTime $date)
    {
        $this->vencimento = $date;
        return $this;
    }

    /**
     * @param double $valor
     * @return ShopFacilService
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @param int $nossonumero
     * @return ShopFacilService
     */
    public function setNossoNumero($nossonumero)
    {
        $this->nossonumero = $nossonumero;
        return $this;
    }

    /**
     * @param int $agencia
     * @return ShopFacilService
     */
    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;
        return $this;
    }

    /**
     * @param int $conta
     * @return ShopFacilService
     */
    public function setConta($conta)
    {
        $this->conta = $conta;
        return $this;
    }

    /**
     * @param int $carteira
     * @return ShopFacilService
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    /**
     * @param Pagador $pagador
     * @return ShopFacilService
     */
    public function setPagador(Pagador $pagador = null)
    {
        $this->pagador = $pagador;
        return $this;
    }

    /**
     * @param Beneficiario $beneficiario
     * @return ShopFacilService
     */
    public function setBeneficiario(Beneficiario $beneficiario = null)
    {
        $this->beneficiario = $beneficiario;
        return $this;
    }

    /**
     * @param string $clientId
     * @return ShopFacilService
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @param string $clientId
     * @return ShopFacilService
     */
    public function setSecretId($clientId)
    {
        $this->secretId = $clientId;
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
     * @return  \DateTime
     */
    public function getEmissao()
    {
        if (is_null($this->emissao)) {
            throw new \InvalidArgumentException('Data Emissäo inválido.');
        }
        return $this->emissao;
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
        return str_pad($this->carteira, 2, "0", STR_PAD_LEFT);
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
     * @return string|\Exception
     */
    private function getAgencia()
    {
        if (is_null($this->agencia)) {
            throw new \InvalidArgumentException('Agência inválido.');
        }
        return $this->agencia;
    }

    /**
     * @return string
     */
    private function getConta()
    {
        if (is_null($this->conta)) {
            throw new \InvalidArgumentException('Conta inválido.');
        }
        return $this->conta;
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
     * @return ShopFacilService
     */
    public function setJuros(Juros $juros): ShopFacilService
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
     * @return ShopFacilService
     */
    public function setMulta(Multa $multa): ShopFacilService
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
     * @return ShopFacilService
     */
    public function setDesconto(Desconto $desconto): ShopFacilService
    {
        array_push($this->desconto, $desconto);
        return $this;
    }

    /**
     * @return int
     */
    public function getPrazoDevolucao()
    {
        return $this->prazodevolucao;
    }

    /**
     * @param mixed $prazodevolucao
     * @return ShopFacilService
     */
    public function setPrazoDevolucao(int $prazodevolucao)
    {
        $this->prazodevolucao = $prazodevolucao;
        return $this;
    }


    public function send()
    {

        try {


            $endereco = new \stdClass();
            $endereco->cep = Helper::number($this->pagador->getCep());
            $endereco->logradouro = substr(Helper::ascii($this->pagador->getLogradouro()), 0, 70);
            $endereco->numero = $this->pagador->getNumero();
            $endereco->complemento = substr(Helper::ascii($this->pagador->getComplemento()), 0, 20);
            $endereco->bairro = substr(Helper::ascii($this->pagador->getBairro()), 0, 50);
            $endereco->cidade = substr(Helper::ascii($this->pagador->getCidade()), 0, 100);
            $endereco->uf = substr(Helper::ascii($this->pagador->getUf()), 0, 2);

            $pagador = new \stdClass();
            $pagador->nome = substr(Helper::ascii($this->pagador->getNome()), 0, 150);
            $pagador->documento = $this->pagador->getDocumento();
            $pagador->tipo_documento = $this->pagador->getTipoDocumento() === 'CPF' ? '1' : '2';
            $pagador->endereco = $endereco;


            $informacoes = new \stdClass();

            $informacoes->especie = '99';
            $informacoes->aceite = 'N';

            $informacoes->tipo_decurso_prazo = '1';
            $informacoes->qtde_dias_decurso = $this->getPrazoDevolucao();
            $informacoes->tipo_emissao_papeleta = '2';

            $juros = $this->juros;
            if (!is_null($this->juros)) {
                $interval_juros = date_diff($this->getVencimento(), $juros->getData());
                if ($juros->getTipo() === $this->juros::Isento) {
                    $informacoes->perc_juros = 0;
                    $informacoes->valor_juros = 0;
                    $informacoes->qtde_dias_juros = 0;
                } elseif ($juros->getTipo() === $this->juros::Diario) {
                    $informacoes->valor_juros = str_pad(number_format($juros->getValor(), 5, '', ''), 8, "0", STR_PAD_LEFT);
                    $informacoes->qtde_dias_juros = $interval_juros->format('%a');
                } elseif ($juros->getTipo() === $this->juros::Mensal) {
                    $informacoes->perc_juros = str_pad(number_format($juros->getValor(), 2, '', ''), 8, "0", STR_PAD_LEFT);
                    $informacoes->qtde_dias_juros = $interval_juros->format('%a');
                } else {
                    throw new \InvalidArgumentException('Código do tipo de juros inválido.');
                }
            }


            $multa = $this->multa;
            if (!is_null($this->multa)) {
                $interval_multa = date_diff($this->getVencimento(), $multa->getData());
                $informacoes->perc_multa_atraso = str_pad(number_format($multa->getPercentual(), 5, '', ''), 8, "0", STR_PAD_LEFT);
                $informacoes->valor_multa_atraso = 0;
                $informacoes->qtde_dias_multa_atraso = $interval_multa->format('%a');
            }

            if (count($this->desconto) > 0) {
                if (count($this->desconto) > 3) {
                    throw new \InvalidArgumentException('Quantidade desconto informado maior que 3.');
                }
                foreach ($this->desconto as $x => $desconto) {
                    if ($desconto->getTipo() === $desconto::Valor) {
                        $informacoes->{'data_limite_desconto_' . ($x + 1)} = $desconto->getData()->format('d.m.Y');
                        $informacoes->{'valor_desconto_' . ($x + 1)} = str_pad(number_format($desconto->getValor(), 5, '', ''), 8, "0", STR_PAD_LEFT);
                    } elseif ($desconto->getTipo() === $desconto::Percentual) {
                        $informacoes->{'data_limite_desconto_' . ($x + 1)} = $desconto->getData()->format('d.m.Y');
                        $informacoes->{'perc_desconto_' . ($x + 1)} = str_pad(number_format($desconto->getValor(), 5, '', ''), 8, "0", STR_PAD_LEFT);
                    } else {
                        throw new \InvalidArgumentException('Código do tipo de desconto inválido.');
                    }
                }
            }


            $boleto = new \stdClass();
            $boleto->carteira = (string)$this->getCarteira();
            $boleto->nosso_numero = (string)$this->getNossoNumero();
            $boleto->numero_documento = (string)$this->getNossoNumero();
            $boleto->data_emissao = $this->getEmissao()->format('Y-m-d');
            $boleto->data_vencimento = $this->getVencimento()->format('Y-m-d');
            $boleto->valor_titulo = (string)number_format($this->getValor(), 2, '', '');
            $boleto->pagador = $pagador;

            //$boleto->informacoes_opcionais = $informacoes; COMO PROBLEMA

            $registo = new \stdClass();
            $registo->merchant_id = $this->getClientId();
            $registo->boleto = $boleto;
            $registo->token_request_confirmacao_registro = rand(188889999, 288889999);


            $client = new Client(['verify' => false]);
            $res = $client->request('POST', 'https://meiosdepagamentobradesco.com.br/apiregistro/api', [
                'json' => $registo,
                'auth' => [$this->getClientId(), $this->getSecretId()]
            ]);

            if ($res->getStatusCode() === 200) {
                $retorno = $res->getBody()->getContents();
                $retorno = json_decode($retorno);
                if($retorno->status->codigo === 930051){
                    throw new InvalidArgumentException($retorno->status->codigo, trim($retorno->status->mensagem));
                }

                // $this->setLinhadigitavel($retorno->linhaDigitavel);
                // $this->setCodigobarras($retorno->cdBarras);
            } else if ($res->getStatusCode() === 201) {
                $boleto = new Bradesco($this->getVencimento(), $this->getValor(), $this->getNossoNumero(), $this->getCarteira(),$this->getAgencia(), $this->getConta(), $this->getConta());
            }

        } catch (RequestException $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500, $e);
        }

    }
}