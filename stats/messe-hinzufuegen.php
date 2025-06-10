<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $start = $_POST['start_datum'];
    $ende = $_POST['end_datum'];

    $stmt = $conn->prepare("INSERT INTO messen (name, start_datum, end_datum) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $start, $ende);
    $stmt->execute();
    $msg = "Messe hinzugefügt!";
}
?>
<!DOCTYPE html>
<html lang="de" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Messe hinzufügen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h1 class="mb-4 text-light">Messe hinzufügen</h1>
        <a href="stats.php" class="btn btn-secondary mb-4">Zurück zur Statistik</a>
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <div class="card bg-dark text-light">
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="name" placeholder="Messe-Name" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="start_datum" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="end_datum" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Hinzufügen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
