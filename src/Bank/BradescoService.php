<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/07/2017
 * Time: 09:05
 */

namespace Boleto\Bank;


use Boleto\Entity\Beneficiario;
use Boleto\Entity\Certificado;
use Boleto\Entity\Juros;
use Boleto\Entity\Multa;
use Boleto\Entity\Pagador;
use Boleto\Exception\InvalidArgumentException;
use Cache\Adapter\Apcu\ApcuCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BradescoService implements InterfaceBank
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
     * @var Certificado
     */
    private $certificado;


    /**
     * @var Juros
     */
    private $juros;

    /**
     * @var Multa
     */
    private $multa;

    private $cache;

    /**
     * BradescoService constructor.
     * @param string $vencimento
     * @param string $valor
     * @param string $nossonumero
     * @param string $carteira
     * @param string $agencia
     * @param string $conta
     * @param Pagador $pagador
     * @param Beneficiario $beneficiario
     * @param Certificado $certificado
     */
    public function __construct(\DateTime $vencimento = null, $valor = null, $nossonumero = null, $carteira = null, $agencia = null, $conta = null, Pagador $pagador = null, Beneficiario $beneficiario = null, Certificado $certificado = null)
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
        $this->certificado = $certificado;
    }

    /**
     * @param \DateTime $date
     * @return BradescoService
     */
    public function setEmissao(\DateTime $date)
    {
        $this->emissao = $date;
        return $this;
    }

    /**
     * @param \DateTime $date
     * @return BradescoService
     */
    public function setVencimento(\DateTime $date)
    {
        $this->vencimento = $date;
        return $this;
    }

    /**
     * @param double $valor
     * @return BradescoService
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
        return $this;
    }

    /**
     * @param int $nossonumero
     * @return BradescoService
     */
    public function setNossoNumero($nossonumero)
    {
        $this->nossonumero = $nossonumero;
        return $this;
    }

    /**
     * @param int $agencia
     * @return BradescoService
     */
    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;
        return $this;
    }

    /**
     * @param int $conta
     * @return BradescoService
     */
    public function setConta($conta)
    {
        $this->conta = $conta;
        return $this;
    }

    /**
     * @param int $carteira
     * @return BradescoService
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;
        return $this;
    }

    /**
     * @param Pagador $pagador
     * @return BradescoService
     */
    public function setPagador(Pagador $pagador = null)
    {
        $this->pagador = $pagador;
        return $this;
    }

    /**
     * @param Beneficiario $pagador
     * @return BradescoService
     */
    public function setBeneficiario(Beneficiario $beneficiario = null)
    {
        $this->beneficiario = $beneficiario;
        return $this;
    }

    /**
     * @param $certificado Certificado
     * @return BradescoService
     */
    public function setCertificado(Certificado $certificado)
    {
        $this->certificado = $certificado;
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
     * @return string
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
     * @return BradescoService
     */
    public function setJuros(Juros $juros): BradescoService
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
     * @return BradescoService
     */
    public function setMulta(Multa $multa): BradescoService
    {
        $this->multa = $multa;
        return $this;
    }

    /**
     * @return Certificado
     */
    private function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @return string
     */
    private function getNumeroNegociacao()
    {
        return $this->agencia . str_pad($this->conta, 14, "0", STR_PAD_LEFT);
    }


    public function send()
    {

        try {

            $arr = new \stdClass();
            $arr->nuCPFCNPJ = $this->beneficiario->getDocumentoRaiz();
            $arr->filialCPFCNPJ = $this->beneficiario->getDocumentoFilial();
            $arr->ctrlCPFCNPJ = $this->beneficiario->getDocumentoControle();
            $arr->cdTipoAcesso = '2';
            $arr->clubBanco = '0';
            $arr->cdTipoContrato = '0';
            $arr->nuSequenciaContrato = '0';
            $arr->idProduto = (string)$this->getCarteira();
            $arr->nuNegociacao = $this->getNumeroNegociacao();
            $arr->cdBanco = '237';
            $arr->eNuSequenciaContrato = '0';
            $arr->tpRegistro = '1';
            $arr->cdProduto = '0';
            $arr->nuTitulo = (string)$this->getNossoNumero();
            $arr->nuCliente = (string)$this->getNossoNumero();
            $arr->dtEmissaoTitulo = $this->getVencimento()->format('d.m.Y');
            $arr->dtVencimentoTitulo = $this->getVencimento()->format('d.m.Y');
            $arr->tpVencimento = '0';
            $arr->vlNominalTitulo = (string)$this->getValor();
            $arr->cdEspecieTitulo = '99';
            $arr->tpProtestoAutomaticoNegativacao = '0';
            $arr->prazoProtestoAutomaticoNegativacao = '0';
            $arr->controleParticipante = '';
            $arr->cdPagamentoParcial = '';
            $arr->qtdePagamentoParcial = '0';

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
            $arr->cepPagador = $this->pagador->getCepPrefixo();
            $arr->complementoCepPagador = $this->pagador->getCepSufixo();
            $arr->bairroPagador = $this->pagador->getBairro();
            $arr->municipioPagador = $this->pagador->getCidade();
            $arr->ufPagador = $this->pagador->getUf();
            $arr->cdIndCpfcnpjPagador = $this->pagador->getTipoDocumento() === 'CPF' ? '1' : '2';
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


            $multa = $this->multa;
            if (!is_null($this->multa)) {
                $interval_multa = date_diff($this->getVencimento(), $multa->getData());
                $arr->percentualMulta = str_pad(number_format($multa->getPercentual(), 5, '', ' '), 8, "0", STR_PAD_LEFT);
                $arr->vlMulta = '0';
                $arr->qtdeDiasMulta = $interval_multa->format('%a');
            } else {
                $arr->percentualMulta = '0';
                $arr->vlMulta = '0';
                $arr->qtdeDiasMulta = '0';
            }


            $juros = $this->juros;
            if (!is_null($this->juros)) {
                $interval_juros = date_diff($this->getVencimento(), $juros->getData());
                if ($juros->getTipo() === $this->juros::Isento) {
                    $arr->percentualJuros = '0';
                    $arr->vlJuros = '0';
                    $arr->qtdeDiasJuros = '0';
                } elseif ($juros->getTipo() === $this->juros::Diario) {
                    $arr->percentualJuros = str_pad(number_format($juros->getValor(), 5, '', ' '), 8, "0", STR_PAD_LEFT);
                    $arr->vlJuros = '0';
                    $arr->qtdeDiasJuros = $interval_juros->format('%a');
                } elseif ($juros->getTipo() === $this->juros::Mensal) {
                    $arr->percentualJuros = str_pad(number_format($juros->getValor(), 5, '', ' '), 8, "0", STR_PAD_LEFT);
                    $arr->vlJuros = '0';
                    $arr->qtdeDiasJuros = $interval_juros->format('%a');
                } else {
                    throw new \InvalidArgumentException('Código do tipo de juros inválido.');
                }
            } else {
                $arr->percentualJuros = '0';
                $arr->vlJuros = '0';
                $arr->qtdeDiasJuros = '0';
            }

            $json = json_encode($arr);

            $base64 = $this->certificado->signText($json);


            $client = new Client(['verify' => false]);
            $res = $client->request('POST', 'https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulo', [
                'body' => $base64
            ]);

            if ($res->getStatusCode() === 200) {
                $retorno = $res->getBody()->getContents();
                $doc = new \DOMDocument();
                $doc->loadXML($retorno);
                $retorno = $doc->getElementsByTagName('return')->item(0)->nodeValue;
                $retorno = preg_replace('/, }/i', '}', $retorno);
                $retorno = json_decode($retorno);
                if (!empty($retorno->cdErro)) {
                    throw new InvalidArgumentException($retorno->cdErro, trim($retorno->msgErro));

                }

                $this->setLinhadigitavel($retorno->linhaDigitavel);
                $this->setCodigobarras($retorno->cdBarras);
            }


        } catch (RequestException $e) {
            echo $e->getMessage() . PHP_EOL;


        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;;
        }

    }
}