<?php

class DbConnect {
    private $host = null;
    private $username = null;
    private $password = null;
    private $database = null;
    private $connection;

    // Constructor to establish the database connection
    public function __construct() {
        $this->set_config();
        $this->connect();
    }
    public function __destruct() {
        $this->closeConnection();
    }
    private function set_config()
    {
        $this->host = get_config("sql::ip");
        $this->username = get_config("sql::username");
        $this->password = get_config("sql::password");
        $this->database = get_config("sql::database");
    }
    // Establish the database connection
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database}";
            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    // Get the database connection
    public function getConnection() {
        return $this->connection;
    }

    // Close the database connection
    public function closeConnection() {
        if ($this->connection) {
            $this->connection = null;
        }
    }
}

function sqlnew()
{
    $db = new DbConnect();
    return $db->getConnection();
}
$conn = sqlnew();

$sql = 
"   CREATE TABLE IF NOT EXISTS userv_account (
        id INT AUTO_INCREMENT PRIMARY KEY,
        account_name VARCHAR(50),
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        password VARCHAR(255),
        email VARCHAR(255),
        activated VARCHAR(50),
        last_login DATETIME,
        registered_at TIMESTAMP,
        roles TEXT
    );
    CREATE TABLE IF NOT EXISTS userv_account_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(255) NOT NULL,
        meta_name VARCHAR(255),
        meta_value TEXT
    );
    CREATE TABLE IF NOT EXISTS userv_channel (
        id INT AUTO_INCREMENT PRIMARY KEY,
        channel_name VARCHAR(50),
        registered_at TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS userv_channel_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        channel_id INT NOT NULL,
        meta_name VARCHAR(255),
        meta_value TEXT
    );
";

try {
    $conn->exec($sql);
} catch (PDOException $e) {
    die("Error creating tables: Please contact the system administrator");
}
$conn->exec($sql);

?>
