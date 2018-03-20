<?php
/**
 * Created by PhpStorm.
 * User: elvis
 * Date: 17/03/2018
 * Time: 14:16
 */

namespace Boleto\Entity;


class Certificado
{

    private $signcert;
    private $privkey;

    /**
     * Certificado constructor.
     */
    public function __construct($file, $password)
    {
        $pfx = file_get_contents($file);
        if (!openssl_pkcs12_read($pfx, $result, $password)) {
            throw new \Exception('Não foi possível ler o certificado .pfx');
        }

        $this->signcert = openssl_x509_read($result['cert']);
        $this->privkey = openssl_pkey_get_private($result['pkey'], $password);

        return $this;
    }

    /**
     * @return bool|resource
     */
    public function getSignCert()
    {
        return $this->signcert;
    }

    /**
     * @return bool|resource
     */
    public function getPrivKey()
    {
        return $this->privkey;
    }

    public function signText($txt)
    {
        try {

            //https://github.com/BoletoNet/boletonet/issues/306

            $file = tempnam(sys_get_temp_dir(), 'php');
            $file_sign = tempnam(sys_get_temp_dir(), 'php');
            file_put_contents($file, $txt);

            openssl_pkcs7_sign($file, $file_sign, $this->signcert, $this->privkey, [], PKCS7_BINARY | PKCS7_TEXT);

            $signature = file_get_contents($file_sign);
            $parts = preg_split("#\n\s*\n#Uis", $signature);
            $base64 = $parts[1];

            unlink($file);
            unlink($file_sign);

            return $base64;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }


}