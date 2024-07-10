<?php
session_start();
include 'config.php';
include 'brapi.php';

$users = [];
$stmt = $conn->prepare("SELECT id, username FROM usuarios");
$stmt->execute();
$result = $stmt->get_result();

while ($user = $result->fetch_assoc()) {
    $userId = $user['id'];
    $username = $user['username'];

    $acoesStmt = $conn->prepare("SELECT * FROM acoes WHERE user_id = ?");
    $acoesStmt->bind_param("i", $userId);
    $acoesStmt->execute();
    $acoesResult = $acoesStmt->get_result();

    $totalInvested = 0;
    $totalPnL = 0;
    $totalMonthlyYield = 0;
    $acoesArray = [];

    while ($acao = $acoesResult->fetch_assoc()) {
        $symbol = $acao['symbol'];
        $purchasePrice = $acao['purchase_price'];
        $quantity = $acao['quantity'];
        $dividendValue = isset($acao['dividend_value']) ? $acao['dividend_value'] : 0;

        $currentPrice = get_stock_price($symbol);

        $totalValue = $currentPrice * $quantity;
        $gainLoss = ($currentPrice - $purchasePrice) * $quantity;
        $totalDividends = $dividendValue * $quantity;
        $monthlyYield = ($dividendValue / $purchasePrice) * 100;

        $totalInvested += $purchasePrice * $quantity;
        $totalPnL += $gainLoss;
        $totalMonthlyYield += $monthlyYield * $quantity;

        $acoesArray[] = [
            'symbol' => $symbol,
            'purchase_price' => $purchasePrice,
            'current_price' => $currentPrice,
            'quantity' => $quantity,
            'total_value' => $totalValue,
            'gain_loss' => $gainLoss,
            'dividend_value' => $dividendValue,
            'total_dividends' => $totalDividends,
            'monthly_yield' => $monthlyYield
        ];
    }

    $users[] = [
        'username' => $username,
        'total_invested' => number_format($totalInvested, 2),
        'total_pnl' => number_format($totalPnL, 2),
        'total_monthly_yield' => number_format($totalMonthlyYield, 2),
        'acoes' => $acoesArray
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main2.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script src="js/main.js"></script>
    <title>Resumo de Carteiras</title>

</head>

<body>
    <header>
        <div class="menu">
            <div class="w10"><img src="images/Marca d'água branca 3.png" alt="Logo"></div>
        </div>
    </header>
    <div class="fundo">
        <div class="container">
            <img src="images/Marcapreta.png" alt="Imagem de Fundo" class="background-image">
            <div class="main-content">
                <h1>Resumo de Carteiras</h1>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Carteira</th>
                            <th>Total Investido</th>
                            <th>P&L Total</th>
                            <th>Total Rendimento Mensal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td>R$ <?= htmlspecialchars($user['total_invested']) ?></td>
                                <td class="<?= $user['total_pnl'] >= 0 ? 'positive' : 'negative' ?>">R$ <?= htmlspecialchars($user['total_pnl']) ?></td>
                                <td><?= htmlspecialchars($user['total_monthly_yield']) ?>%</td>
                            </tr>
                            <tr class="expand-content">
                                <td colspan="4">
                                    <div class="expand-header" onclick="toggleExpandContent(this)">
                                        <?= htmlspecialchars($user['username']) ?> <b>(Clique para fechar)</b>
                                    </div>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Ativo</th>
                                                <th>Preço de Compra</th>
                                                <th>Preço Atual</th>
                                                <th>Quantidade</th>
                                                <th>Valor Total</th>
                                                <th>P&L</th>
                                                <th>Dividendo</th>
                                                <th>Total Dividendos</th>
                                                <th>Rendimento Mensal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user['acoes'] as $acao) : ?>
                                                <tr>
                                                    <td class="cinza" ><?= htmlspecialchars($acao['symbol']) ?></td>
                                                    <td>R$ <?= htmlspecialchars($acao['purchase_price']) ?></td>
                                                    <td>R$ <?= htmlspecialchars($acao['current_price']) ?></td>
                                                    <td><?= htmlspecialchars($acao['quantity']) ?></td>
                                                    <td>R$ <?= htmlspecialchars($acao['total_value']) ?></td>
                                                    <td class="<?= $acao['gain_loss'] >= 0 ? 'positive' : 'negative' ?>">R$ <?= htmlspecialchars($acao['gain_loss']) ?></td>
                                                    <td>R$ <?= htmlspecialchars($acao['dividend_value']) ?></td>
                                                    <td>R$ <?= htmlspecialchars($acao['total_dividends']) ?></td>
                                                    <td><?= htmlspecialchars($acao['monthly_yield']) ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="expand-footer">
                                        <p>Total Investido: <b>R$ <?= htmlspecialchars($user['total_invested']) ?></b></p>
                                        <p>P&L Total: <b>R$ <?= htmlspecialchars($user['total_pnl']) ?></b></p>
                                        <p>Total Rendimento Mensal: <b><?= htmlspecialchars($user['total_monthly_yield']) ?>%</b></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="log">
            <div class="container2">
                <h1>Login</h1>
                <form method="POST" action="login.php">
                    <div class="input-box">
                        <p>Usuário:</p> <input placeholder="Carteira..." type="text" name="username" required>
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-box">
                        <p>Senha:</p> <input placeholder="Senha..." type="password" name="password" required>
                        <i class='bx bxs-lock-alt'></i>
                    </div>
                    <button class="btn" type="submit">ENTRAR</button>
                </form>
            </div>
            <div class="container2">
                <h1>Nova Carteira</h1>
                <form method="POST" action="register.php">
                    <div class="input-box">
                        <p>Carteira:</p> <input placeholder="Nome da carteira" type="text" name="username" required>
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-box">
                        <p>Senha:</p> <input placeholder="Senha da carteira" type="password" name="password" required>
                        <i class='bx bxs-lock-alt'></i>
                    </div>
                    <button class="btn" type="submit">REGISTRAR</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>