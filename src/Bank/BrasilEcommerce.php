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
use GuzzleHttp\Exception\ClientException;
use function GuzzleHttp\Psr7\str;
use Meng\AsyncSoap\Guzzle\Factory;

class BrasilEcommerce implements InterfaceBank
{


    /**
     * @var \DateTime
     */
    private $vencimento, $emissao;
    private $valor;
    private $convenio;
    private $contrato;
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

   
    private $cache;


    /**
     * BrasilEcommerce constructor.
     * @param string $vencimento
     * @param string $valor
     * @param string $convenio
     * @param string $contrato
     * @param string $variacaocarteira
     * @param string $nossonumero
     * @param string $carteira
     */
    public function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero = null, $carteira = null, $convenio = null, $contrato = null, $variacaocarteira = null, Pagador $pagador = null)
    {
        $this->cache = new ApcuCachePool();

        $this->emissao = new \DateTime();
        $this->vencimento = $vencimento;
        $this->valor = $valor;
        $this->nossonumero = $nossonumero;
        $this->carteira = $carteira;
        $this->convenio = $convenio;
        $this->contrato = $contrato;
        $this->variacaocarteira = $variacaocarteira;
        $this->pagador = $pagador;
    }

    /**
     * @param \DateTime $date
     * @return BrasilEcommerce
     */
    public function setEmissao(\DateTime $date)
    {
        $this->emissao = $date;
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return BrasilEcommerce
     */
    public function setVencimento(\DateTime $date)
    {
        $this->vencimento = $date;
        return $this;
    }

    /**
     * @param double $valor
     * @return BrasilEcommerce
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @param int $nossonumero
     * @return BrasilEcommerce
     */
    public function setNossoNumero($nossonumero)
    {
        $this->nossonumero = $nossonumero;
        return $this;
    }

    /**
     * @param int $convenio
     * @return BrasilEcommerce
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }

    /**
     * @param int $contrato
     * @return BrasilEcommerce
     */
    public function setContrato($contrato)
    {
        $this->contrato = $contrato;
        return $this;
    }

    /**
     * @param int $variacaocarteira
     * @return BrasilEcommerce
     */
    public function setVariacaoCarteira($variacaocarteira)
    {
        $this->variacaocarteira = $variacaocarteira;
        return $this;
    }

    /**
     * @param int $carteira
     * @return BrasilEcommerce
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    /**
     * @param Pagador $pagador
     * @return BrasilEcommerce
     */
    public function setPagador(Pagador $pagador = null)
    {
        $this->pagador = $pagador;
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
        return $this->emissao;
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
    private function getContrato()
    {
        if (is_null($this->contrato)) {
            throw new \InvalidArgumentException('Contrato inválido.');
        }
        return $this->contrato;
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
     * @return BrasilEcommerce
     */
    public function setJuros(Juros $juros): BrasilEcommerce
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
     * @return BrasilEcommerce
     */
    public function setMulta(Multa $multa): BrasilEcommerce
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
     * @return BrasilEcommerce
     */
    public function setDesconto(Desconto $desconto): BrasilEcommerce
    {
        array_push($this->desconto, $desconto);
        return $this;
    }


    public function send()
    {

        try {

            $arr = new \StdClass();
            $arr->idConv = $this->getConvenio();
            $arr->refTran = $this->getContrato() . str_pad($this->getNossoNumero(), 10, "0", STR_PAD_LEFT);
            $arr->valor = number_format($this->getValor(), 2, '', '');
            $arr->qtdPontos = '';
            $arr->dtVenc = $this->getVencimento()->format('dmY');
            $arr->tpPagamento = 2;
            $arr->cpfCnpj = Helper::number($this->pagador->getDocumento());
            $arr->indicadorPessoa = $this->pagador->getTipoDocumento() === 'CPF' ? 1 : 2;
            $arr->tpDuplicata = 'DM';
            /*
            $arr->valorDesconto = '';
            $arr->dataLimiteDesconto = '';
            $arr->urlRetorno = '';
            $arr->urlInforma = '';
            */
            $arr->urlRetorno = 'http://localhost';
            $arr->nome = strtoupper(Helper::ascii($this->pagador->getNome()));
            $arr->endereco = strtoupper(Helper::ascii($this->pagador->getLogradouro()));
            $arr->cidade = strtoupper(Helper::ascii($this->pagador->getCidade()));
            $arr->uf = $this->pagador->getUf();
            $arr->cep = Helper::number($this->pagador->getCep());
            $arr->msgLoja = '';
            $arr->formato = 'XML';

            $client = new Client(['defaults' => ['verify' => false]]);
            $res = $client->request('POST', 'https://mpag.bb.com.br/site/mpag/', [
                'form_params' => $arr
            ]);

            $body = $res->getBody()->getContents();
            file_put_contents('I:/projetos/boleto/test/'.$this->getNossoNumero().'.pdf', $body);
            if ($res->getStatusCode() === 200) {
                $json = $res->getBody()->getContents();
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500, $e);
        }

    }


}