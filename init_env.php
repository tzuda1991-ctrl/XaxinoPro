<?php
$envPath = '/var/www/html/core/.env';
if (!file_exists($envPath)) {
    if (file_exists('/var/www/html/core/.env.example')) {
        copy('/var/www/html/core/.env.example', $envPath);
    } else {
        touch($envPath);
    }
}

$appUrl = getenv('APP_URL');
if (!$appUrl) {
    if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
        $appUrl = 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN');
    } elseif (getenv('RAILWAY_STATIC_URL')) {
        $appUrl = 'https://' . getenv('RAILWAY_STATIC_URL');
    } else {
        $appUrl = 'http://localhost:' . (getenv('PORT') ?: 8080);
    }
}

$host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
$port = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306');
$db   = getenv('DB_DATABASE') ?: (getenv('MYSQLDATABASE') ?: 'railway');
$user = getenv('DB_USERNAME') ?: (getenv('MYSQLUSER') ?: 'root');
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('MYSQLPASSWORD') ?: '');

// Parse MYSQL_URL or DATABASE_URL if present and host was default
$mysqlUrl = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($mysqlUrl && ($host === '127.0.0.1' || $host === 'localhost')) {
    $parsed = parse_url($mysqlUrl);
    if ($parsed) {
        $host = $parsed['host'] ?? $host;
        $port = $parsed['port'] ?? $port;
        $user = $parsed['user'] ?? $user;
        $pass = $parsed['pass'] ?? $pass;
        $db   = ltrim($parsed['path'] ?? $db, '/');
    }
}

echo "=== Xaxino Environment Setup ===\n";
echo "APP_URL: $appUrl\n";
echo "Database: $user@$host:$port/$db\n";

$vars = [
    'APP_NAME' => 'Xaxino',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'APP_URL' => $appUrl,
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => $host,
    'DB_PORT' => (string)$port,
    'DB_DATABASE' => $db,
    'DB_USERNAME' => $user,
    'DB_PASSWORD' => $pass,
    'SESSION_DRIVER' => 'file',
    'CACHE_STORE' => 'file',
    'QUEUE_CONNECTION' => 'sync'
];

$content = file_exists($envPath) ? file_get_contents($envPath) : '';
foreach ($vars as $k => $v) {
    // Escape quotes inside value
    $escaped = str_replace('"', '\"', $v);
    if (preg_match("/^#?\s*{$k}=.*/m", $content)) {
        $content = preg_replace("/^#?\s*{$k}=.*/m", "{$k}=\"{$escaped}\"", $content);
    } else {
        $content .= "\n{$k}=\"{$escaped}\"";
    }
}
file_put_contents($envPath, $content);

// Now test MySQL and auto-import if available
if ($host !== '127.0.0.1' && $host !== 'localhost') {
    echo "Connecting to MySQL at $host:$port...\n";
    $connected = false;
    $pdo = null;
    for ($i = 1; $i <= 15; $i++) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ]);
            echo "✅ Connected to MySQL successfully!\n";
            $connected = true;
            break;
        } catch (Exception $e) {
            echo "Waiting for MySQL ($i/15): " . $e->getMessage() . "\n";
            sleep(2);
        }
    }

    if ($connected && $pdo) {
        try {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $count = count($tables);
            echo "Database tables found: $count\n";
            if ($count === 0 && file_exists('/var/www/html/database.sql')) {
                echo "Empty database detected. Importing database.sql...\n";
                $cmd = sprintf(
                    'mysql --protocol=tcp -h %s -P %s -u %s -p%s %s < /var/www/html/database.sql 2>&1',
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($user),
                    escapeshellarg($pass),
                    escapeshellarg($db)
                );
                exec($cmd, $out, $ret);
                if ($ret === 0) {
                    echo "✅ database.sql imported successfully!\n";
                } else {
                    echo "⚠️ Import notices/warnings: " . implode(" ", $out) . "\n";
                }
            }
        } catch (Exception $e) {
            echo "⚠️ Notice during table check: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⚠️ Note: Could not establish MySQL connection after 15 attempts. Continuing container startup...\n";
    }
} else {
    echo "ℹ️ Localhost MySQL detected. Skipping remote DB check.\n";
}
