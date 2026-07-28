<?php
// search.php
header('Content-Type: application/json; charset=utf-8');

// Konfigurace databáze
$host = 'localhost';
$db   = 'tvuj_nazev_databaze'; // ZMĚŇ na název své DB
$user = 'tvuj_uzivatel';       // ZMĚŇ na svého DB uživatele
$pass = 'tvoje_heslo';          // ZMĚŇ na své heslo
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['error' => 'Chyba připojení k databázi']);
    exit;
}

$query  = isset($_GET['q']) ? trim($_GET['q']) : '';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = 30; // Počet záznamů na jednu dávku
$offset = ($page - 1) * $limit;

// Hledáme pouze pokud má dotaz alespoň 2 znaky
if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Vyhledávání (používáme N:N strukturu nebo jednoduchou tabulku - zde ukázka s JOIN na N:N)
$sql = "SELECT DISTINCT e.en_pojem, e.en_popis, c.cz_pojem, c.cz_popis
        FROM en_terms e
        JOIN term_relations r ON e.id = r.en_id
        JOIN cz_terms c ON c.id = r.cz_id
        WHERE e.en_pojem LIKE :q OR c.cz_pojem LIKE :q
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':q', '%' . $query . '%', PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$results = $stmt->fetchAll();

echo json_encode($results);