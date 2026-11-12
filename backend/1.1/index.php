<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO("sqlite:" . DIR . "/database.sqlite", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS countries (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL,
        capital TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cities (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        country_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
    )");

    $check = $pdo->query("SELECT COUNT(*) FROM countries")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO countries (name, slug, capital) VALUES ('Ukraine', 'ukraine', 'Kyiv'), ('Poland', 'poland', 'Warsaw')");
        $pdo->exec("INSERT INTO cities (country_id, name) VALUES (1, 'Kyiv'), (1, 'Lviv'), (2, 'Warsaw'), (2, 'Krakow')");
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Помилка підключення до БД: " . $e->getMessage()]);
    exit;
}

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$path = trim($path, '/');
$segments = explode('/', $path);

$endpointIndex = array_search('1.1', $segments);
if ($endpointIndex !== false && isset($segments[$endpointIndex + 1])) {
    $action = $segments[$endpointIndex + 1];
    $param = $segments[$endpointIndex + 2] ?? null;

    if ($action === 'getcountrieslist') {
        $stmt = $pdo->query("SELECT id, name, slug FROM countries");
        echo json_encode($stmt->fetchAll());
        exit;
    } elseif ($action === 'getcountry' && $param) {
        $stmt = $pdo->prepare("SELECT * FROM countries WHERE slug = ?");
        $stmt->execute([$param]);
        $country = $stmt->fetch();
        if ($country) {
            $stmtCities = $pdo->prepare("SELECT id, name FROM cities WHERE country_id = ?");
            $stmtCities->execute([$country['id']]);
            $country['cities'] = $stmtCities->fetchAll();
            echo json_encode($country);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Країну не знайдено"]);
        }
        exit;
    }
}

http_response_code(404);
echo json_encode(["error" => "Невідомий шлях API"]);
?>