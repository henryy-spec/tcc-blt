// O fluxo de pagamento da versão PHP é processado por pagamento.php + php/assinatura.php.
// Este arquivo fica disponível para futuras integrações com um gateway real.
function selecionarMetodo(metodo) {
    const pix = document.getElementById('pixInfo');
    const cartao = document.getElementById('cartaoInfo');
    if (pix) pix.style.display = metodo === 'pix' ? 'block' : 'none';
    if (cartao) cartao.style.display = metodo === 'cartao' ? 'block' : 'none';
}
