<?php
require __DIR__ . '/../vendor/autoload.php';


use Boleto\Entity\Pagador;
use Boleto\Entity\Beneficiario;
use Boleto\Entity\Juros;
use Boleto\Entity\Multa;
use \Boleto\Entity\Certificado;

try {

    $certificado = new Certificado('xxx.pfx', 'xxxx');
    $pagador = new Pagador('Fulano da Silva', '43563609829', 'Rua Antenor Guirlanda', '15', null, 'Casa Verde', 'São Paulo', 'SP', '02514-010');
    $beneficiario = new Beneficiario('Fulano da Silva', '13.954.351/0001-83', 'Rua Antenor Guirlanda', '15', null, 'Casa Verde', 'São Paulo', 'SP', '02514-010');

    $vencimento = new DateTime('2018-03-21');
	
	

    $juros = new Juros(Juros::Mensal, 2, new DateTime('2018-03-22'));
    $multa = new Multa(2, new DateTime('2018-03-22'));

    $bradesco = new \Boleto\Bank\BradescoService();
    $bradesco->setEmissao($vencimento)
        ->setVencimento($vencimento)
        ->setValor(100)
        ->setNossoNumero(1000008)
        ->setCarteira(26)
        ->setAgencia('4852')
        ->setConta('604')
        ->setPagador($pagador)
        ->setBeneficiario($beneficiario)
        ->setCertificado($certificado)
        ->setJuros($juros)
        ->setMulta($multa)
        ->send();

    echo $bradesco->getLinhaDigitavel();

} catch (\Exception $e) {
    echo $e->getMessage();
}