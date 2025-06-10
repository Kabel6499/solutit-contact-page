<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $start = $_POST['start_datum'];
    $ende = $_POST['end_datum'];

    $stmt = $conn->prepare("INSERT INTO messen (name, start_datum, end_datum) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $start, $ende);
    $stmt->execute();
    echo "Messe hinzugefügt!";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Messe hinzufügen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <h1>Messe hinzufügen</h1>
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
            <button type="submit" class="btn btn-primary">Hinzufügen</button>
        </div>
    </form>
</body>
</html>
