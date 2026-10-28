<?php
// Página de SIMULAÇÃO de pagamento: nenhum valor é cobrado e nada é gravado no banco
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?form=login');
    exit;
}

require_once '../php/catalogo.php';

$pedido = $_SESSION['pagamento_pendente'] ?? null;
if (!$pedido) {
    header('Location: meus-agendamentos.php');
    exit;
}

// Link fictício gerado a partir do código do pedido
$link_pagamento = 'https://pagar.aurabela.com.br/checkout/' . $pedido['codigo'];
$data_formatada = date('d/m/Y', strtotime($pedido['data']));

// Monta um código Pix "copia e cola" no padrão BR Code (EMV) com chave fictícia
function campoPix($id, $valor) {
    return $id . sprintf('%02d', strlen($valor)) . $valor;
}
function crc16Pix($texto) {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($texto); $i++) {
        $crc ^= ord($texto[$i]) << 8;
        for ($b = 0; $b < 8; $b++) {
            $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
            $crc &= 0xFFFF;
        }
    }
    return strtoupper(sprintf('%04X', $crc));
}
$codigo_pix = campoPix('00', '01')
    . campoPix('26', campoPix('00', 'br.gov.bcb.pix') . campoPix('01', 'simulacao@aurabela.exemplo'))
    . campoPix('52', '0000')
    . campoPix('53', '986')
    . campoPix('54', number_format($pedido['valor'], 2, '.', ''))
    . campoPix('58', 'BR')
    . campoPix('59', 'AURA BELA')
    . campoPix('60', 'OSASCO')
    . campoPix('62', campoPix('05', $pedido['codigo']))
    . '6304';
$codigo_pix .= crc16Pix($codigo_pix);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Pagamento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>

    <header class="navbar">
        <a href="../index.php" class="logo-text">Aura Bela</a>
        <nav class="nav-links">
            <a href="../index.php">Início</a>
            <a href="quem-somos.php">Quem somos</a>
            <a href="reiki.php">Reiki</a>
            <a href="servicos.php">Serviços</a>
        </nav>
    </header>

    <main class="booking-section">
        <div class="schedule-container payment-container" id="paymentBox">
            <span class="sim-badge">Ambiente de simulação</span>
            <h1 class="auth-title">Pagamento</h1>

            <!-- Resumo do agendamento -->
            <div class="payment-summary">
                <div class="summary-row"><span>Procedimento</span><strong><?= htmlspecialchars($pedido['servico']) ?></strong></div>
                <div class="summary-row"><span>Profissional</span><strong><?= htmlspecialchars($pedido['profissional']) ?></strong></div>
                <div class="summary-row"><span>Data e horário</span><strong><?= $data_formatada ?> às <?= htmlspecialchars($pedido['horario']) ?></strong></div>
                <?php if ($pedido['duracao']): ?>
                <div class="summary-row">
                    <span>Duração</span>
                    <strong class="duration-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <?= formatarDuracao($pedido['duracao']) ?>
                    </strong>
                </div>
                <?php endif; ?>
                <div class="summary-row summary-total"><span>Total</span><strong><?= formatarPreco($pedido['valor']) ?></strong></div>
            </div>

            <!-- Métodos de pagamento -->
            <div class="pay-methods" role="tablist">
                <button type="button" class="pay-method active" data-method="link">Link de pagamento</button>
                <button type="button" class="pay-method" data-method="pix">Pix</button>
            </div>

            <div class="pay-panel active" data-panel="link">
                <p class="pay-hint">Envie ou abra o link abaixo para concluir o pagamento.</p>
                <div class="pay-link-box">
                    <input type="text" id="payLink" value="<?= $link_pagamento ?>" readonly>
                    <button type="button" class="btn-copy" data-copy="payLink">Copiar</button>
                </div>
            </div>

            <div class="pay-panel" data-panel="pix">
                <p class="pay-hint">Escaneie o QR Code no app do seu banco ou use o código Pix copia e cola.</p>
                <div class="pix-qr" id="pixQr" role="img" aria-label="QR Code Pix"></div>
                <div class="pay-link-box">
                    <input type="text" id="pixCode" value="<?= htmlspecialchars($codigo_pix) ?>" readonly>
                    <button type="button" class="btn-copy" data-copy="pixCode">Copiar</button>
                </div>
            </div>

            <button type="button" class="btn-auth-submit" id="btnSimularPagamento">Simular pagamento de <?= formatarPreco($pedido['valor']) ?></button>

            <!-- Estado de sucesso exibido após a simulação -->
            <div class="pay-success d-none" id="paySuccess">
                <div class="pay-success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <h2>Pagamento aprovado!</h2>
                <p>Pedido #<?= $pedido['codigo'] ?> · seu horário está garantido.</p>
                <a href="meus-agendamentos.php?status=sucesso" class="btn-primary">Ver meus agendamentos</a>
            </div>
        </div>
    </main>

    <footer class="footer-simple">
        <div class="footer-bottom">
            <nav class="footer-links">
                <a href="../index.php">Inicio</a>
                <a href="quem-somos.php">Quem somos</a>
                <a href="reiki.php">Reiki</a>
                <a href="servicos.php">Serviços</a>
            </nav>
            <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>
<?php unset($_SESSION['pagamento_pendente']); ?>
