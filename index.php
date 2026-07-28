<?php
// --- PŘIPOJENÍ K MARIADB DATABÁZI ---
$host = 'localhost';
$db   = 'prace_db_tst';    // Zde zadej název své databáze v phpMyAdminu
$user = 'klema';       // Uživatelské jméno
$pass = 'mojeheslo123';           // Heslo do DB

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Chyba připojení k DB: " . $e->getMessage());
}

// Získání hledaného textu z formuláře
$hledanyText = $_GET['q'] ?? '';
$vysledky = [];

if (!empty($hledanyText)) {
    $search = '%' . $hledanyText . '%';
    
    // Prohledáváme všechny sloupce z tvé tabulky
    // Změň 'pojmy' níže na reálný název tvé tabulky v phpMyAdminu
    $sql = "SELECT * FROM pojmy WHERE 
            cz_pojem LIKE ? OR 
            en_pojem LIKE ? OR 
            cz_popis LIKE ? OR 
            en_popis LIKE ? OR 
            cz_zkratka LIKE ? OR 
            en_zkratka LIKE ?";

    $stmt = $pdo->prepare($sql);
    // Předáme vyhledávaný řetězec 6x (pro každý parametr v WHERE)
    $stmt->execute([$search, $search, $search, $search, $search, $search]);
    $vysledky = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Vyhledávání pojmů</title>
  <style>
    body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 0 20px; }
    .search-form { display: flex; gap: 10px; margin-bottom: 20px; }
    input[type="text"] { flex: 1; padding: 10px; font-size: 16px; border: 2px solid #ccc; border-radius: 6px; }
    button { padding: 10px 20px; font-size: 16px; cursor: pointer; background: #0066cc; color: white; border: none; border-radius: 6px; }
    .polozka { padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 12px; background: #fdfdfd; }
    .polozka h3 { margin: 0 0 5px 0; color: #222; }
    .zkratky { color: #888; font-size: 13px; margin-bottom: 8px; }
    .popis { margin: 5px 0; color: #444; font-size: 14px; }
    .lang-block { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee; }
  </style>
</head>
<body>

  <h2>Slovník pojmů a zkratek</h2>

  <form action="index.php" method="GET" class="search-form">
    <input 
      type="text" 
      name="q" 
      placeholder="Hledat český nebo anglický pojem/zkratku..." 
      value="<?= htmlspecialchars($hledanyText) ?>"
    >
    <button type="submit">Hledat</button>
  </form>

  <div class="vysledky">
    <?php if (!empty($hledanyText)): ?>
      <p>Nalezeno výsledků: <strong><?= count($vysledky) ?></strong></p>
      
      <?php if (count($vysledky) > 0): ?>
        <?php foreach ($vysledky as $p): ?>
          <div class="polozka">
            <!-- CZ Název a zkratka -->
            <h3>
              <?= htmlspecialchars($p['cz_pojem']) ?> 
              <?php if (!empty($p['en_pojem'])): ?>
                / <?= htmlspecialchars($p['en_pojem']) ?>
              <?php endif; ?>
            </h3>

            <div class="zkratky">
              <?php if (!empty($p['cz_zkratka'])): ?>CZ zkratka: <strong><?= htmlspecialchars($p['cz_zkratka']) ?></strong> | <?php endif; ?>
              <?php if (!empty($p['en_zkratka'])): ?>EN zkratka: <strong><?= htmlspecialchars($p['en_zkratka']) ?></strong><?php endif; ?>
            </div>

            <!-- CZ Popis -->
            <?php if (!empty($p['cz_popis'])): ?>
              <div class="popis"><strong>CZ:</strong> <?= htmlspecialchars($p['cz_popis']) ?></div>
            <?php endif; ?>

            <!-- EN Popis -->
            <?php if (!empty($p['en_popis'])): ?>
              <div class="popis"><strong>EN:</strong> <?= htmlspecialchars($p['en_popis']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>Pro výraz "<strong><?= htmlspecialchars($hledanyText) ?></strong>" nebylo nic nalezeno.</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</body>
</html>
