<?php
require __DIR__ . '/../vendor/autoload.php';


use Boleto\Entity\Pagador;
use Boleto\Entity\Juros;
use Boleto\Entity\Multa;

try {

    $pagador = new Pagador('Fulano da Silva', '62344900187', 'Rua Antenor Guirlanda', '15', null, 'Casa Verde', 'São Paulo', 'SP', '02514-010');

    $vencimento = new DateTime('2017-09-27');

    $juros = new Juros(Juros::Mensal, 2, $vencimento->modify('+1 day'));
    $multa = new Multa(2, $vencimento->modify('+1 day'));

    $bb = new \Boleto\Bank\BrasilService();
    $bb->setEmissao($vencimento)
        ->setVencimento($vencimento)
        ->setValor(100)
        ->setNossoNumero(1000008)
        ->setCarteira(17)
        ->setConvenio(1014051)
        ->setVariacaoCarteira(19)
        ->setPagador($pagador)
        ->setClientId('')
        ->setSecretId('')
        ->send();

    echo $bb->getLinhaDigitavel();

} catch (\Exception $e) {
    echo $e->getMessage();
}