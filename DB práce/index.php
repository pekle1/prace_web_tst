<?php
// 1. Připojení k MySQL databázi
$host = 'localhost';
$db   = 'nazev_databaze';
$user = 'uzivatel';
$pass = 'heslo';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
    die("Chyba připojení k DB: " . $e->getMessage());
}

// 2. Pokud prohlížeč žádá o vyhledávání přes API
if (isset($_GET['q'])) {
    $dotaz = '%' . $_GET['q'] . '%';
    $stmt = $pdo->prepare("SELECT * FROM produkty WHERE nazev LIKE ? OR popis LIKE ?");
    $stmt->execute([$dotaz, $dotaz]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit; // Ukončíme skript, vrátíme jen JSON data
}
?>

<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <title>Vyhledávání v DB</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .polozka {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
    </style>
</head>

<body>

    <h2>Vyhledávání v databázi</h2>
    <input type="text" id="hledat" placeholder="Napiš co hledáš...">
    <div id="vysledky"></div>

    <script>
        const input = document.getElementById('hledat');
        const vysledkyDiv = document.getElementById('vysledky');

        input.addEventListener('input', async (e) => {
            const dotaz = e.target.value.trim();
            if (!dotaz) {
                vysledkyDiv.innerHTML = '';
                return;
            }

            // Dotaz na PHP backend, který se zeptá MySQL databáze
            const res = await fetch(`index.php?q=${encodeURIComponent(dotaz)}`);
            const data = await res.json();

            if (data.length === 0) {
                vysledkyDiv.innerHTML = '<p>Nic nenalezeno.</p>';
                return;
            }

            vysledkyDiv.innerHTML = data.map(p => `
        <div class="polozka">
          <h3>${p.nazev}</h3>
          <p>${p.popis}</p>
        </div>
      `).join('');
        });
    </script>

</body>

</html>