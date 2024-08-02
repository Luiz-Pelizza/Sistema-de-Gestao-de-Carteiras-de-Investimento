<?php
session_start();
include 'config.php';
include 'brapi.php';
include 'update_dividend.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['dividend_type']) && isset($_POST['dividend_value']) && isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $dividend_type = $_POST['dividend_type'];
    $dividend_value = $_POST['dividend_value'];

    $stmt = $conn->prepare("UPDATE acoes SET dividend_type = ?, dividend_value = ? WHERE id = ?");
    $stmt->bind_param("sdi", $dividend_type, $dividend_value, $update_id);
    $stmt->execute();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_portfolio'])) {
    $stmt = $conn->prepare("DELETE FROM acoes WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    session_destroy();
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT username FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, symbol, purchase_price, quantity, dividend_value, dividend_type FROM acoes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();



$stmt = $conn->prepare("SELECT username FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_action'])) {
        $symbol = strtoupper($_POST['symbol']);
        $purchase_price = $_POST['purchase_price'];
        $quantity = $_POST['quantity'];
        $dividend_type = $_POST['dividend_type'];
        $dividend_value = ($dividend_type === 'manual') ? $_POST['dividend_value'] : get_dividend_value($symbol);

        $stmt = $conn->prepare("INSERT INTO acoes (user_id, symbol, purchase_price, quantity, dividend_value, dividend_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdids", $user_id, $symbol, $purchase_price, $quantity, $dividend_value, $dividend_type);
        $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['remove_id'])) {
        $remove_id = $_POST['remove_id'];

        $stmt = $conn->prepare("DELETE FROM acoes WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $remove_id, $user_id);
        $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['update_dividend'])) {
        $update_id = $_POST['update_id'];
        $dividend_value = $_POST['dividend_value'];
        $dividend_type = $_POST['dividend_type'];

        $stmt = $conn->prepare("UPDATE acoes SET dividend_value = ?, dividend_type = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("dsii", $dividend_value, $dividend_type, $update_id, $user_id);
        $stmt->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$stmt = $conn->prepare("SELECT id, symbol, purchase_price, quantity, dividend_value, dividend_type FROM acoes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$total_invested = 0;
$total_pnl = 0;

while ($row = $result->fetch_assoc()) {
    $current_price = get_stock_price($row['symbol']);
    $total_value = $current_price !== null ? $current_price * $row['quantity'] : 0;
    $gain_loss = $current_price !== null ? ($current_price - $row['purchase_price']) * $row['quantity'] : 0;

    $total_invested += $row['purchase_price'] * $row['quantity'];
    $total_pnl += $gain_loss;

    $dividend_value = isset($row['dividend_value']) ? $row['dividend_value'] : 0;
    $total_dividends = $dividend_value * $row['quantity'];
    $monthly_yield = $row['purchase_price'] != 0 ? ($dividend_value / $row['purchase_price']) * 100 : 0;

    $rows[] = [
        'id' => $row['id'],
        'symbol' => $row['symbol'],
        'purchase_price' => $row['purchase_price'],
        'quantity' => $row['quantity'],
        'current_price' => $current_price,
        'total_value' => $total_value,
        'gain_loss' => number_format($gain_loss, 2),
        'dividend_value' => $dividend_value,
        'total_dividends' => $total_dividends,
        'monthly_yield' => number_format($monthly_yield, 2),
        'dividend_type' => $row['dividend_type']
    ];
}

$username = htmlspecialchars($user['username']);
$total_invested_formatted = number_format($total_invested, 2);
$total_pnl_formatted = number_format($total_pnl, 2);

function get_dividend_value($symbol)
{
    return 0.50;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/main1.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <script src="js/main.js"></script>
    <title>Carteira: <?= $username ?></title>
</head>

<body>
    <header>
        <div class="menu">
            <div class="w10"><img href="" src="images/Marca d'água branca 3.png"></div>
            <div class="itens-menu">
                <form method="POST" action="logout.php">
                    <button class="btn-sair" type="submit">Sair da carteira</button>
                </form>
            </div>
        </div>
    </header>

    <div class="fundo">
        <div class="container2">
            <img src="images/Marcapreta.png" alt="Imagem de Fundo" class="background-image">
            <div class="n"><p><?= $username ?></p></div>
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                <table border="1">
                    <thead>
                        <tr class="cinza">
                            <th>Ativo</th>
                            <th>Preço de Compra</th>
                            <th>Preço Atual</th>
                            <th>Quantidade</th>
                            <th>Valor Total</th>
                            <th>P&L</th>
                            <th>Dividendo</th>
                            <th>Total Dividendos</th>
                            <th>Rendimento Mensal</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th class="cinza"><input placeholder="ATIVO" type="text" name="symbol" required></th>
                            <th><input placeholder="00.00" type="number" step="0.01" name="purchase_price" required></th>
                            <th>N/A</th>
                            <th><input placeholder="00" type="number" name="quantity" required></th>
                            <th>N/A</th>
                            <th>N/A</th>
                            <th>
                                <select style="cursor: pointer;" name="dividend_type" onchange="toggleDividendInput(this)">
                                    <option value="automatic">Automático</option>
                                    <option value="manual">Manual</option>
                                </select>
                                <input placeholder="0,00" type="number" step="0.01" name="dividend_value">
                            </th>
                            <th>N/A</th>
                            <th>N/A</th>
                            <th><button class="ad" type="submit" name="add_action">ADICIONAR</button></th>
                        </tr>
                    </thead>
            </form>
            <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td class="cinza"><?= htmlspecialchars($row['symbol']) ?></td>
                                <td><?= htmlspecialchars($row['purchase_price']) ?></td>
                                <td><?= htmlspecialchars($row['current_price'] !== null ? $row['current_price'] : "N/A") ?></td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td><?= htmlspecialchars($row['total_value']) ?></td>
                                <td class="<?= $row['gain_loss'] >= 0 ? 'positive' : 'negative' ?>"><?= htmlspecialchars($row['gain_loss']) ?></td>
                                <td>
                                <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" id="update_form_<?= $row['id'] ?>">
                                <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="symbol" value="<?= $row['symbol'] ?>">
                                    <select style="cursor: pointer;" name="dividend_type" onchange="toggleDividendInput(this)" data-automatic-value="0">
                                        <option value="automatic" <?= $row['dividend_type'] == 'automatic' ? 'selected' : '' ?>>Automático</option>
                                        <option value="manual" <?= $row['dividend_type'] == 'manual' ? 'selected' : '' ?>>Manual</option>
                                    </select>
                                    <input type="number" step="0.01" name="dividend_value" value="<?= $row['dividend_value'] ?>" <?= $row['dividend_type'] != 'manual' ? 'style="display:none;"' : '' ?>>
                                </form>
                                </td>
                                <td><?= htmlspecialchars($row['total_dividends']) ?></td>
                                <td><?= htmlspecialchars($row['monthly_yield']) ?>%</td>
                                <td>
                                    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
                                        <input type="hidden" name="remove_id" value="<?= $row['id'] ?>">
                                        <button class="bnt-remove" type="submit">REMOVER</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
        <div class="totals">
            <div>Total Investido: R$ <?= $total_invested_formatted ?></div>
            <div><span class="<?= $total_pnl >= 0 ? 'positive' : 'negative' ?>">P&L Total: R$ <?= $total_pnl_formatted ?></span></div>
            <div class="ex">
                <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return confirm('Tem certeza que deseja excluir esta carteira?');">
                    <input type="hidden" name="delete_portfolio">
                    <button class="delete-portfolio" type="submit"><i class="fi fi-sr-file-minus"></i></button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>