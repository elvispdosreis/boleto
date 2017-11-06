<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 06/11/2017
 * Time: 08:53
 */

namespace Boleto\Service;

class CaixaSoapCliente extends \SoapClient
{
    function __construct($wsdl, $options = null)
    {
        if (is_null($options)) {
            $httpHeaders = [
                'http' => [
                    'protocol_version' => 1.1,
                    'header' => "Cache-Control: no-cache"
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $context = stream_context_create($httpHeaders);

            $options = [
                'trace' => TRUE,
                'exceptions' => TRUE,
                'encoding' => 'UTF-8',
                'compression' => \SOAP_COMPRESSION_ACCEPT | \SOAP_COMPRESSION_GZIP,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 15,
                'stream_context' => $context,
                'use' => \SOAP_LITERAL
            ];
        }

        parent::__construct($wsdl, $options);
    }

    function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $request = str_replace('xmlns:ns1="http://caixa.gov.br/sibar/manutencao_cobranca_bancaria/boleto/externo"', 'xmlns:ns1="http://caixa.gov.br/sibar/manutencao_cobranca_bancaria/boleto/externo" xmlns:sib="http://caixa.gov.br/sibar"', $request);

        $request = str_replace('<HEADER>', '<sib:HEADER>', $request);
        $request = str_replace('</HEADER>', '</sib:HEADER>', $request);
        return parent::__doRequest($request, $location, $action, $version, $one_way);
    }
}