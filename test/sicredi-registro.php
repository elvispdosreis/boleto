<?php
require __DIR__ . '/../vendor/autoload.php';

use Boleto\Bank\SicrediService;
use Boleto\Entity\Pagador;
use Boleto\Entity\Juros;
use Boleto\Entity\Multa;
use Boleto\Entity\Desconto;

try {
    $pagador    = new Pagador('Fulano da Silva', '37465283000157', 'Av França, 123', '123', null, 'Casa Verde', 'Porto Alegre', 'RS', '91760110', '51-999999999');
    $vencimento = new DateTime('2021-04-30');

    // Multa e Juros precisa ser implementando só aceita valores expresso em reais
    $juros      = new Juros(Juros::Mensal, 1, new DateTime('2021-04-24'));
    $multa = new Multa(2, new DateTime('2018-03-22'));

    $desconto1  = new Desconto(Desconto::Valor, 10.99, new DateTime('2021-04-21'));
    $desconto2  = new Desconto(Desconto::Valor, 5, new DateTime('2021-04-22'));
    $desconto3  = new Desconto(Desconto::Valor, 1.99, new DateTime('2021-04-23'));

    $sicredi = new SicrediService();
    $sicredi ->setAgencia('0800')
        ->setPosto('05')
        ->setCedente('39000')
        ->setCodigoSacadorAvalista('000')
        ->setSeuNumero('219400154')
        ->setNossoNumero('219400154')
        ->setVencimento($vencimento)
        ->setValor(228.15)
        ->setJuros($juros)
        ->setMensagem('Mensagem gerada pelo teste de integracao')
        ->setCodigoMensagem('')
        ->setInformativo('Informativo gerado pelo teste de integracao')
        ->setPagador($pagador)
        ->setDesconto($desconto1)
        ->setDesconto($desconto2)
        ->setDesconto($desconto3)
        ->setToken('')
        ->send();
    echo $sicredi->getLinhaDigitavel();
} catch (\Exception $e) {
    echo $e->getMessage();
}
