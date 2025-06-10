<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'config.php';

// Auth-Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Messe-Auswahl
$messen = [];
$messe_id = isset($_GET['messe_id']) ? $_GET['messe_id'] : 'all';
$zeitraum = isset($_GET['zeitraum']) ? $_GET['zeitraum'] : 'heute';

// Messen laden
$res = $conn->query("SELECT id, name FROM messen ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $messen[] = $row;
}

// Besucherzahlen & Chartdaten
$chart_labels = [];
$chart_data = [];

if ($messe_id === 'all') {
    if ($zeitraum === 'heute') {
        $heute = date('Y-m-d');
        // Besucher heute (alle Messen)
        $sql = "SELECT COUNT(DISTINCT ip_address) as besucher FROM besuche WHERE DATE(zeitstempel) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $heute);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $besucher = $row['besucher'] ?? 0;
        $stmt->close();

        // Chart: Besucher pro Stunde heute (alle Messen)
        $sql = "SELECT HOUR(zeitstempel) as stunde, COUNT(DISTINCT ip_address) as besucher 
                FROM besuche WHERE DATE(zeitstempel) = ? 
                GROUP BY HOUR(zeitstempel) ORDER BY stunde";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $heute);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $chart_labels[] = sprintf('%02d:00', $row['stunde']);
            $chart_data[] = $row['besucher'];
        }
        $stmt->close();
    } else {
        // Besucher gesamt (alle Messen)
        $sql = "SELECT COUNT(DISTINCT ip_address) as besucher FROM besuche";
        $res = $conn->query($sql);
        $row = $res->fetch_assoc();
        $besucher = $row['besucher'] ?? 0;

        // Chart: Besucher pro Tag (alle Messen)
        $sql = "SELECT DATE(zeitstempel) as tag, COUNT(DISTINCT ip_address) as besucher 
                FROM besuche GROUP BY DATE(zeitstempel) ORDER BY tag";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $chart_labels[] = $row['tag'];
            $chart_data[] = $row['besucher'];
        }
    }
} else {
    if ($zeitraum === 'heute') {
        $heute = date('Y-m-d');
        // Besucher heute (eine Messe)
        $sql = "SELECT COUNT(DISTINCT ip_address) as besucher FROM besuche WHERE messe_id = ? AND DATE(zeitstempel) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $messe_id, $heute);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $besucher = $row['besucher'] ?? 0;
        $stmt->close();

        // Chart: Besucher pro Stunde heute (eine Messe)
        $sql = "SELECT HOUR(zeitstempel) as stunde, COUNT(DISTINCT ip_address) as besucher 
                FROM besuche WHERE messe_id = ? AND DATE(zeitstempel) = ? 
                GROUP BY HOUR(zeitstempel) ORDER BY stunde";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $messe_id, $heute);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $chart_labels[] = sprintf('%02d:00', $row['stunde']);
            $chart_data[] = $row['besucher'];
        }
        $stmt->close();
    } else {
        // Besucher gesamt (eine Messe)
        $sql = "SELECT COUNT(DISTINCT ip_address) as besucher FROM besuche WHERE messe_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $messe_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $besucher = $row['besucher'] ?? 0;
        $stmt->close();

        // Chart: Besucher pro Tag (eine Messe)
        $sql = "SELECT DATE(zeitstempel) as tag, COUNT(DISTINCT ip_address) as besucher 
                FROM besuche WHERE messe_id = ? GROUP BY DATE(zeitstempel) ORDER BY tag";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $messe_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $chart_labels[] = $row['tag'];
            $chart_data[] = $row['besucher'];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Messe-Statistik</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body, .bg-dark {
            background-color: #212529 !important;
        }
        .card.bg-dark {
            border-color: #343a40;
        }
    </style>
</head>
<body class="bg-dark text-light">
<div class="container py-4">
    <h1 class="mb-4 text-light">Messe-Statistik</h1>
<form class="row g-3 mb-4 align-items-end" method="get">
    <div class="col-md-4">
        <label for="messe_id" class="form-label">Messe auswählen</label>
        <select class="form-select" id="messe_id" name="messe_id" onchange="this.form.submit()">
            <option value="all" <?= $messe_id === 'all' ? 'selected' : '' ?>>Alle Messen</option>
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
            <option value="heute" <?= $zeitraum == 'heute' ? 'selected' : '' ?>>Heute</option>
            <option value="all" <?= $zeitraum == 'all' ? 'selected' : '' ?>>All Time</option>
        </select>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <a href="messe-hinzufuegen.php" class="btn btn-warning w-100">Neue Messe erstellen</a>
    </div>
</form>


    <div class="card bg-dark text-light mb-4">
        <div class="card-body">
            <h4 class="card-title">
                Besucher <?= $zeitraum == 'heute' ? 'heute' : 'gesamt' ?>:
                <span class="text-primary"><?= $besucher ?></span>
            </h4>
        </div>
    </div>

    <div class="card bg-dark text-light">
        <div class="card-body">
            <canvas id="statsChart" height="80"></canvas>
            <?php if ($messe_id !== 'all'): ?>
            <div class="d-flex justify-content-end mt-3">
                <form method="get" class="m-0">
                    <input type="hidden" name="messe_id" value="<?= htmlspecialchars($messe_id) ?>">
                    <input type="hidden" name="zeitraum" value="<?= htmlspecialchars($zeitraum) ?>">
                    <input type="hidden" name="reset" value="1">
                    <button type="submit" class="btn btn-danger">Statistik für diese Messe zurücksetzen</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['reset']) && $_GET['reset'] == 1 && isset($_GET['messe_id']) && $_GET['messe_id'] !== 'all'): ?>
        <?php
            $id = (int)$_GET['messe_id'];
            $conn->query("DELETE FROM besuche WHERE messe_id = $id");
            echo '<div class="alert alert-success my-4">Statistik für diese Messe wurde zurückgesetzt.</div>';
        ?>
    <?php endif; ?>
</div>

<script>
Chart.register({
    id: 'customCanvasBackgroundColor',
    beforeDraw: (chart) => {
        const ctx = chart.canvas.getContext('2d');
        ctx.save();
        ctx.globalCompositeOperation = 'destination-over';
        ctx.fillStyle = '#212529'; // Bootstrap bg-dark
        ctx.fillRect(0, 0, chart.width, chart.height);
        ctx.restore();
    }
});

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
            backgroundColor: '#0d6efd',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false,
                labels: {
                    color: '#fff'
                }
            },
            tooltip: {
                backgroundColor: '#222',
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#444',
                borderWidth: 1
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: <?= $zeitraum == 'heute' ? "'Stunde'" : "'Tag'" ?>,
                    color: '#fff'
                },
                ticks: {
                    color: '#fff'
                },
                grid: {
                    color: 'rgba(255,255,255,0.1)'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Besucher',
                    color: '#fff'
                },
                beginAtZero: true,
                ticks: {
                    color: '#fff'
                },
                grid: {
                    color: 'rgba(255,255,255,0.1)'
                }
            }
        }
    }
});
</script>
</body>
</html>
