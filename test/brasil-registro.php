<?php
require __DIR__ . '/../vendor/autoload.php';


use Boleto\Entity\Pagador;


try {

    $pagador = new Pagador('Fulano da Silva', '62344900187', 'Rua Antenor Guirlanda', '15', null, 'Casa Verde', 'São Paulo', 'SP', '02514-010');

    $vencimento = new DateTime('2017-09-27');

    $bb = new \Boleto\Bank\BrasilService();
    $bb->setEmissao($vencimento)
        ->setVencimento($vencimento)
        ->setValor(100)
        ->setNossoNumero(1000008)
        ->setCarteira(17)
        ->setConvenio(1014051)
        ->setVariacaoCarteira(19)
        ->setPagador($pagador)
        ->setClientId('eyJpZCI6IjgwNDNiNTMtZjQ5Mi00YyIsImNvZGlnb1B1YmxpY2Fkb3IiOjEwOSwiY29kaWdvU29mdHdhcmUiOjEsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxfQ')
        ->setSecretId('eyJpZCI6IjBjZDFlMGQtN2UyNC00MGQyLWI0YSIsImNvZGlnb1B1YmxpY2Fkb3IiOjEwOSwiY29kaWdvU29mdHdhcmUiOjEsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxLCJzZXF1ZW5jaWFsQ3JlZGVuY2lhbCI6MX0')
        ->send();

    echo $bb->getLinhaDigitavel();

} catch (\Exception $e) {
    echo $e->getMessage();
}