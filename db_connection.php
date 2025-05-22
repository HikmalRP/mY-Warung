<?php
class DBConnection {
    private $host = 'localhost';
    private $user = 'Masukin User';
    private $password = 'Masukin Password';
    private $database = 'Masukin Password';
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}
?>
