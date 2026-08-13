<?php
declare(strict_types=1);

/*
 * CONFIGURAÇÃO DE E-MAIL DA BLUE LIGHT
 *
 * Este arquivo usa a função mail() nativa do PHP.
 * Em hospedagens, normalmente basta configurar o e-mail do domínio no painel.
 * Em XAMPP/localhost, o envio real depende da configuração SMTP do PHP/servidor.
 */

const BLT_NOME_SITE = 'Blue Light';
const BLT_EMAIL_REMETENTE = 'no-reply@seudominio.com.br';

/* URL pública da pasta PHP. Exemplo:
 * https://seudominio.com.br/php
 */
const BLT_URL_PHP = 'https://seudominio.com.br/php';

function enviarEmailRecuperacao(string $destinatario, string $nome, string $link): bool
{
    $assunto = 'Recuperação de senha - Blue Light';

    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');

    $html = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif;background:#0b1020;color:#fff;padding:30px">'
          . '<div style="max-width:600px;margin:auto;background:#151b2e;padding:30px;border-radius:12px">'
          . '<h1>Blue Light</h1>'
          . '<p>Olá, ' . $nomeSeguro . '.</p>'
          . '<p>Recebemos uma solicitação para alterar a senha da sua conta.</p>'
          . '<p><a href="' . $linkSeguro . '" style="display:inline-block;padding:12px 20px;background:#4f7cff;color:#fff;text-decoration:none;border-radius:8px">Redefinir minha senha</a></p>'
          . '<p>Este link é válido por 30 minutos e só pode ser utilizado uma vez.</p>'
          . '<p>Se você não solicitou esta alteração, ignore este e-mail.</p>'
          . '</div></body></html>';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . BLT_NOME_SITE . " <" . BLT_EMAIL_REMETENTE . ">\r\n";
    $headers .= "Reply-To: " . BLT_EMAIL_REMETENTE . "\r\n";

    return mail($destinatario, '=?UTF-8?B?' . base64_encode($assunto) . '?=', $html, $headers);
}
