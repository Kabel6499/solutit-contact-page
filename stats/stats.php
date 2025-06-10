<?php
session_start();
include 'config.php';

// Auth-Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Messe-Auswahl
$messen = [];
$messe_id = isset($_GET['messe_id']) ? (int)$_GET['messe_id'] : 1;
$zeitraum = isset($_GET['zeitraum']) ? $_GET['zeitraum'] : 'all';

// Messen laden
$res = $conn->query("SELECT id, name FROM messen ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $messen[] = $row;
}

// Zeitraum-Filter
$where = "WHERE messe_id = ?";
$params = [$messe_id];
$types = "i";

if ($zeitraum === '7days') {
    $where .= " AND zeitstempel >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($zeitraum === 'tag' && isset($_GET['datum'])) {
    $where .= " AND DATE(zeitstempel) = ?";
    $params[] = $_GET['datum'];
    $types .= "s";
}

// Besucherzahlen holen
$sql = "SELECT COUNT(DISTINCT ip_address) as besucher FROM besuche $where";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->bind_result($besucher);
$stmt->fetch();
$stmt->close();

// Für Chart: Besucher pro Tag laden
$chart_labels = [];
$chart_data = [];
if ($zeitraum === 'all') {
    $sql = "SELECT DATE(zeitstempel) as tag, COUNT(DISTINCT ip_address) as besucher 
            FROM besuche WHERE messe_id = ? GROUP BY DATE(zeitstempel) ORDER BY tag";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messe_id);
} elseif ($zeitraum === '7days') {
    $sql = "SELECT DATE(zeitstempel) as tag, COUNT(DISTINCT ip_address) as besucher 
            FROM besuche WHERE messe_id = ? AND zeitstempel >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
            GROUP BY DATE(zeitstempel) ORDER BY tag";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $messe_id);
} elseif ($zeitraum === 'tag' && isset($_GET['datum'])) {
    $sql = "SELECT DATE(zeitstempel) as tag, COUNT(DISTINCT ip_address) as besucher 
            FROM besuche WHERE messe_id = ? AND DATE(zeitstempel) = ? 
            GROUP BY DATE(zeitstempel) ORDER BY tag";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $messe_id, $_GET['datum']);
}
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $chart_labels[] = $row['tag'];
    $chart_data[] = $row['besucher'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Messe-Statistik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4">Messe-Statistik</h1>
    <form class="row g-3 mb-4" method="get">
        <div class="col-md-4">
            <label for="messe_id" class="form-label">Messe auswählen</label>
            <select class="form-select" id="messe_id" name="messe_id" onchange="this.form.submit()">
                <?php foreach ($messen as $messe): ?>
                    <option value="<?= $messe['id'] ?>" <?= $messe_id == $messe['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($messe['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label for="zeitraum" class="form-label">Zeitraum</label>
            <select class="form-select" id="zeitraum" name="zeitraum" onchange="this.form.submit()">
                <option value="all" <?= $zeitraum == 'all' ? 'selected' : '' ?>>All Time</option>
                <option value="7days" <?= $zeitraum == '7days' ? 'selected' : '' ?>>Letzte 7 Tage</option>
                <option value="tag" <?= $zeitraum == 'tag' ? 'selected' : '' ?>>Bestimmter Tag</option>
            </select>
        </div>
        <?php if ($zeitraum == 'tag'): ?>
        <div class="col-md-4">
            <label for="datum" class="form-label">Datum</label>
            <input type="date" class="form-control" id="datum" name="datum" value="<?= isset($_GET['datum']) ? htmlspecialchars($_GET['datum']) : '' ?>" onchange="this.form.submit()">
        </div>
        <?php endif; ?>
    </form>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Besucher gesamt: <span class="text-primary"><?= $besucher ?></span></h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <canvas id="statsChart" height="80"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('statsChart').getContext('2d');
const statsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Besucher',
            data: <?= json_encode($chart_data) ?>,
            fill: false,
            borderColor: '#0d6efd',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        }
    }
});
</script>
</body>
</html>
