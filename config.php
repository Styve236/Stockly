// Stockly Configuration - DB Connection
// Support for Vercel PostgreSQL and local MySQL fallback
if (getenv('POSTGRES_HOST')) {
    // Vercel PostgreSQL
    define('DB_HOST', getenv('POSTGRES_HOST'));
    define('DB_NAME', getenv('POSTGRES_DATABASE'));
    define('DB_USER', getenv('POSTGRES_USER'));
    define('DB_PASS', getenv('POSTGRES_PASSWORD'));
    $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME;
} else {
    // Local MySQL (XAMPP)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'stockly');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Get current user role
function getUserRole($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}
?>

