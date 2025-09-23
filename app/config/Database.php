<?php
class Database
{
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'ems';

    public $conn;

    public function getConnection()
    {
        if ($this->conn == null) {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

            if ($this->conn->connect_error) {
                die("Connection failed: " . $this->conn->connect_error);
            }
        }
        return $this->conn;
    }
}
