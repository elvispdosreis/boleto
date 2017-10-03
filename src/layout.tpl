<!doctype html>
<html>
<head>
    <meta charset="iso-8859-1">
    <title>Pedido N&deg; {$boleto.nossonumero}</title>
    <style>
        table, ul {
            margin: 0 auto;
        }

        tr.value > td.banco {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
        }

        tr.title > td {
            font-size: 9px;
            font-family: arial, verdana;
            padding: 3px 3px 0px 3px;
        }

        tr.value > td {
            border-bottom: 1px solid #000000;
            font-family: arial, verdana;
            font-size: 11px;
            font-weight: bold;
            padding: 0px 3px 1px 3px;
        }

        tr.title > td, tr.value > td {
            border-right: 1px solid #000000;
        }

        tr.title > td:last-child, tr.value > td:last-child {
            border-right: none;
        }

        tr.title.sacado > td, tr.value.sacado > td {
            border-right: none;
        }

        tr.value.barcode > td {
            border-bottom: none;
        }

        tr.value > td.logo {
            padding: 0;
            margin: 0;
            font-size: 0px;
            line-height: normal;
            vertical-align: bottom;
        }

        td.linha {
            text-align: right;
            vertical-align: bottom;
        }

        span.linhadigitavel {
            font-size: 15px !important;
            text-align: right;
            float: right;
        }

        td.dashed {
            border-top: 1px dashed #000000;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        ul.instrucoes {
            font-size: 11px;
            font-family: verdana, arial;
        }
    </style>
</head>

<body>
<table border="0" cellpadding="0" cellspacing="0" width="711">
    <tr>
        <td width="128"></td>
        <td width="16"></td>
        <td width="18"></td>
        <td width="33"></td>
        <td width="17"></td>
        <td width="32"></td>
        <td width="12"></td>
        <td width="24"></td>
        <td width="44"></td>
        <td width="25"></td>
        <td width="3"></td>
        <td width="33"></td>
        <td width="39"></td>
        <td width="35"></td>
        <td width="45"></td>
        <td width="4"></td>
        <td width="58"></td>
        <td width="145"></td>
        <td width="1"></td>
    </tr>
    <tr>
        <td colspan="18" height="80">
            <ul class=instrucoes>
                <li>Imprima em papel A4 ou Carta</li>
                <li>Utilize margens m&iacute;nimas a direita e a esquerda</li>
                <li>Recorte na linha pontilhada</li>
                <li>N&atilde;o rasure o c&oacute;digo de barras</li>
            </ul>
        </td>
    </tr>
    <tr class="value">
        <td colspan="2" class="logo"><img src="imgs/<?php echo $varBoleto['codigo_banco_com_dv']; ?>.jpg" border="0">
        </td>
        <td colspan="2" class="banco"><?php echo $varBoleto['codigo_banco_com_dv']; ?></td>
        <td colspan="14" class="linha"><img src="data:image/jpeg;base64,{$boleto.getLinhaDigitavelBase64()}"></td>
    </tr>
    <tr class="title">
        <td colspan="9">Cedente</td>
        <td colspan="5">Ag&ecirc;ncia/C&oacute;digo do Cedente&nbsp;</td>
        <td colspan="2">Esp&eacute;cie</td>
        <td>Quantidade</td>
        <td>Nosso n&uacute;mero</td>
    </tr>
    <tr class="value">
        <td colspan="9">{$boleto.cedente.nome}</td>
        <td colspan="5" class="text-right"><?php echo $varBoleto['agencia_codigo']; ?></td>
        <td colspan="2" class="text-right">R$</td>
        <td>&nbsp;</td>
        <td class="text-right"><?php echo $varBoleto['nossonumero']; ?></td>
    </tr>
    <tr class="title">
        <td colspan="3">N&uacute;mero do documento</td>
        <td colspan="7">CPF/CNPJ</td>
        <td colspan="6">Vencimento</td>
        <td colspan="2">Valor documento</td>
    </tr>
    <tr class="value">
        <td colspan="3" class="text-right"><?php echo $varBoleto['id_venda']; ?></td>
        <td colspan="7" class="text-right"><?php echo $varBoleto['cedente_cpfcnpj']; ?></td>
        <td colspan="6" class="text-right"><?php echo $varBoleto['vencimento']; ?></td>
        <td colspan="2" class="text-right"><?php echo $varBoleto['total']; ?></td>
    </tr>
    <tr class="title">
        <td>(-) Desconto / Abatimentos</td>
        <td colspan="6">(-) Outras dedu&ccedil;&otilde;es</td>
        <td colspan="5">(+) Mora / Multa</td>
        <td colspan="4">(+) Outros acr&eacute;scimos</td>
        <td colspan="2">(=) Valor cobrado</td>
    </tr>
    <tr class="value">
        <td>&nbsp;</td>
        <td colspan="6">&nbsp;</td>
        <td colspan="5">&nbsp;</td>
        <td colspan="4">&nbsp;</td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr class="title">
        <td colspan="18">Sacado</td>
    </tr>
    <tr class="value">
        <td colspan="18"><?php echo $varBoleto['sacado_nome']; ?></td>
    </tr>
    <tr class="title sacado">
        <td colspan="14">Demonstrativo</td>
        <td colspan="4" class="text-right">Autentica&ccedil;&atilde;o Mec&acirc;nica - <strong>Recibo Sacado</strong>
        </td>
    </tr>
    <tr class="value">
        <td colspan="18" height="180" style="vertical-align:top; border-bottom:none;">
            {$boleto.demostrativo1}<br>
            {$boleto.demostrativo2}<br>
            {$boleto.demostrativo3}
        </td>
    </tr>
    <tr class="title">
        <td colspan="18" class="dashed text-right"> Corte na linha pontilhada</td>
    </tr>
    <tr class="title">
        <td colspan="18" height="60"></td>
    </tr>
    <tr class="value">
        <td colspan="2" class="logo"><img src="imgs/<?php echo $varBoleto['codigo_banco_com_dv']; ?>.jpg" border="0">
        </td>
        <td colspan="2" class="banco"><?php echo $varBoleto['codigo_banco_com_dv']; ?></td>
        <td colspan="14" class="linha"><img src="data:image/jpeg;base64,{$boleto.getLinhaDigitavelBase64()}"></td>
    </tr>
    <tr class="title">
        <td colspan="17">Local de Pagamento</td>
        <td>Vencimento</td>
    </tr>
    <tr class="value">
        <td colspan="17">Pag&aacute;vel em qualquer Banco at&eacute; o vencimento</td>
        <td class="text-right">{$boleto.vencimento}</td>
    </tr>
    <tr class="title">
        <td colspan="17">Cedente</td>
        <td>Ag&ecirc;ncia/C&oacute;digo do Cedente</td>
    </tr>
    <tr class="value">
        <td colspan="17"><?php echo $varBoleto['cedente_nome']; ?></td>
        <td class="text-right"><?php echo $varBoleto['agencia_codigo']; ?></td>
    </tr>
    <tr class="title">
        <td colspan="2">Data do Documento</td>
        <td colspan="6">N&uacute;mero do Documento</td>
        <td colspan="3">Esp&eacute;cie doc.</td>
        <td colspan="2">Aceite</td>
        <td colspan="4">Data do Processamento</td>
        <td>Nosso N&uacute;mero</td>
    </tr>
    <tr class="value">
        <td colspan="2" class="text-right"><?php echo $varBoleto['data_compra']; ?></td>
        <td colspan="6" class="text-right"><?php echo $row['id_venda']; ?></td>
        <td colspan="3">&nbsp;</td>
        <td colspan="2" class="text-right">N</td>
        <td colspan="4" class="text-right"><?php echo date('d/m/Y H:i:s'); ?></td>
        <td class="text-right"><?php echo $varBoleto['nossonumero']; ?></td>
    </tr>
    <tr class="title">
        <td colspan="2">Uso do Banco</td>
        <td colspan="3">Carteira</td>
        <td colspan="3">Esp&eacute;cie</td>
        <td colspan="5">Quantidade</td>
        <td colspan="4">(x) Valor</td>
        <td>(=) Valor documento</td>
    </tr>
    <tr class="value">
        <td colspan="2">&nbsp;</td>
        <td colspan="3" class="text-right"><?php echo $varBoleto['carteira']; ?></td>
        <td colspan="3" class="text-right">RS</td>
        <td colspan="5">&nbsp;</td>
        <td colspan="4">&nbsp;</td>
        <td class="text-right"><?php echo $varBoleto['total']; ?></td>
    </tr>
    <tr class="title">
        <td colspan="17" style="vertical-align:top">Instru&ccedil;&otilde;es (Texto de responsabilidade do cedente)</td>
        <td>(-) Desconto / Abatimentos</td>
    </tr>
    <tr class="value">
        <td colspan="17" rowspan="9" style="vertical-align:top">
            {$boleto.instrucoes1}<br>{$boleto.instrucoes2}<br>{$boleto.instrucoes3}<br>{$boleto.instrucoes4}
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr class="title">
        <td>(-) Outras dedu&ccedil;&otilde;es</td>
    </tr>
    <tr class="value">
        <td>&nbsp;</td>
    </tr>
    <tr class="title">
        <td>(+) Mora / Multa</td>
    </tr>
    <tr class="value">
        <td>&nbsp;</td>
    </tr>
    <tr class="title">
        <td>(+) Outros acr&eacute;scimos</td>
    </tr>
    <tr class="value">
        <td>&nbsp;</td>
    </tr>
    <tr class="title">
        <td>(=) Valor cobrado</td>
    </tr>
    <tr class="value">
        <td>&nbsp;</td>
    </tr>
    <tr class="title sacado">
        <td colspan="17">Sacado</td>
        <td>&nbsp;</td>
    </tr>
    <tr class="value sacado">
        <td colspan="17">{$boleto.sacado.nome} - {$boleto.sacado.cpf}<br>
            {$boleto.sacado.endereco}, {$boleto.sacado.numero} {$boleto.sacado.complemento}
            <br>
            {$boleto.sacado.bairro} - {$boleto.sacado.cidade} - {$boleto.sacado.estado} / Cep: {$boleto.sacado.cep}
            - {$boleto.sacado.pais}
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr class="title sacado">
        <td colspan="14">Sacador/Avalista</td>
        <td colspan="4" class="text-right">Autentica&ccedil;&atilde;o Mec&acirc;nica - <strong>Ficha de Compensa&ccedil;&atilde;o</strong>
        </td>
    </tr>
    <tr class="value barcode">
        <td colspan="18"><img src="data:image/jpeg;base64,{$boleto.getCodigoBarrasBase64()}"></td>
    </tr>
</table>
</body>
</html>