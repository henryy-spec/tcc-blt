<?php
declare(strict_types=1);

/*
 * Configuração de e-mail da Blue Light.
 * Usa PHPMailer + Gmail SMTP. As credenciais ficam no .env da raiz do projeto.
 */

const BLT_NOME_SITE = 'Blue Light';

function bltCarregarEnv(): void
{
    static $carregado = false;
    if ($carregado) return;
    $carregado = true;

    $arquivo = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($arquivo) || !is_readable($arquivo)) return;

    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) return;

    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || str_starts_with($linha, '#') || !str_contains($linha, '=')) continue;
        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);
        if ($valor !== '' && (($valor[0] ?? '') === '"' || ($valor[0] ?? '') === "'") && substr($valor, -1) === $valor[0]) {
            $valor = substr($valor, 1, -1);
        }
        if ($chave !== '' && getenv($chave) === false) {
            putenv($chave . '=' . $valor);
        }
    }
}

bltCarregarEnv();

function bltEnv(string $chave, string $padrao = ''): string
{
    $valor = getenv($chave);
    return $valor === false ? $padrao : trim((string)$valor);
}

function bltMailConfig(): array
{
    return [
        'host' => bltEnv('BLT_SMTP_HOST', 'smtp.gmail.com'),
        'port' => (int)bltEnv('BLT_SMTP_PORT', '587'),
        'username' => bltEnv('BLT_SMTP_USERNAME'),
        'password' => bltEnv('BLT_SMTP_PASSWORD'),
        'encryption' => strtolower(bltEnv('BLT_SMTP_ENCRYPTION', bltEnv('MAIL_ENCRYPTION', 'tls'))),
        'from' => bltEnv('BLT_EMAIL_FROM', bltEnv('BLT_SMTP_USERNAME')),
        'from_name' => bltEnv('BLT_EMAIL_FROM_NAME', BLT_NOME_SITE),
        'url_base' => rtrim(bltEnv('BLT_URL_BASE', 'http://localhost/BlueLight'), '/'),
    ];
}

function bltCriarMailer(): PHPMailer\PHPMailer\PHPMailer
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';
    if (!is_file($autoload)) {
        throw new RuntimeException('PHPMailer não está instalado. Execute composer install.');
    }
    require_once $autoload;

    $cfg = bltMailConfig();
    if ($cfg['username'] === '' || $cfg['password'] === '') {
        throw new RuntimeException('BLT_SMTP_USERNAME/BLT_SMTP_PASSWORD não estão configurados no .env.');
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = $cfg['host'];
    $mail->Port = $cfg['port'];
    $mail->SMTPAuth = true;
    $mail->Username = $cfg['username'];
    $mail->Password = $cfg['password'];
    $mail->SMTPSecure = $cfg['encryption'] === 'ssl'
        ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->setFrom($cfg['from'], $cfg['from_name']);
    $mail->addReplyTo($cfg['from'], $cfg['from_name']);
    return $mail;
}

function enviarEmailHtml(string $destinatario, string $assunto, string $html): bool
{
    try {
        $mail = bltCriarMailer();
        $mail->addAddress($destinatario);
        $mail->Subject = $assunto;
        $mail->isHTML(true);
        $mail->Body = $html;
        $mail->AltBody = trim(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $html)));
        return $mail->send();
    } catch (Throwable $e) {
        error_log('[BLT SMTP] ' . $e->getMessage());
        return false;
    }
}

function enviarEmailRecuperacao(string $destinatario, string $nome, string $link): bool
{
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $linkSeguro = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    $html = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif;background:#0b1020;color:#fff;padding:30px">'
          . '<div style="max-width:600px;margin:auto;background:#151b2e;padding:30px;border-radius:12px">'
          . '<h1>Blue Light</h1><p>Olá, ' . $nomeSeguro . '.</p>'
          . '<p>Recebemos uma solicitação para alterar a senha da sua conta.</p>'
          . '<p><a href="' . $linkSeguro . '" style="display:inline-block;padding:12px 20px;background:#4f7cff;color:#fff;text-decoration:none;border-radius:8px">Redefinir minha senha</a></p>'
          . '<p>Este link é válido por 30 minutos e só pode ser utilizado uma vez.</p>'
          . '<p>Se você não solicitou esta alteração, ignore este e-mail.</p></div></body></html>';
    return enviarEmailHtml($destinatario, 'Recuperação de senha - Blue Light', $html);
}

function enviarEmailLembreteRenovacao(string $destinatario, string $nome, ?string $plano, ?string $assinaturaAte): bool
{
    $cfg = bltMailConfig();
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $planoSeguro = htmlspecialchars($plano ?: 'Blue Light', ENT_QUOTES, 'UTF-8');
    $dataSeguro = htmlspecialchars($assinaturaAte ? date('d/m/Y', strtotime($assinaturaAte)) : 'em breve', ENT_QUOTES, 'UTF-8');
    $urlPagamento = $cfg['url_base'] . '/pagamento.php';
    $html = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"></head><body style="font-family:Arial,sans-serif;background:#0b1020;color:#fff;padding:30px">'
          . '<div style="max-width:600px;margin:auto;background:#151b2e;padding:30px;border-radius:12px">'
          . '<h1 style="margin-top:0">Blue Light</h1><p>Olá, ' . $nomeSeguro . '.</p>'
          . '<p>Seu plano <strong>' . $planoSeguro . '</strong> está próximo do vencimento' . ($assinaturaAte ? ' em <strong>' . $dataSeguro . '</strong>.' : '.') . '</p>'
          . '<p>Para continuar aproveitando a plataforma e seus jogos, acesse sua conta e faça a renovação.</p>'
          . '<p style="margin-top:24px"><a href="' . htmlspecialchars($urlPagamento, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:12px 20px;background:#4f7cff;color:#fff;text-decoration:none;border-radius:8px">Renovar meu plano</a></p>'
          . '<p style="color:#aab3c5;font-size:13px">Este é um lembrete enviado pela administração da Blue Light.</p></div></body></html>';
    return enviarEmailHtml($destinatario, 'Seu plano Blue Light está perto de vencer', $html);
}
