<?php
include('Auth.php');
class DbConnection extends Auth
{
    public $DB = false;
    public function __construct()
    {
        if (!$this->DB) {
            $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) {
                die("Error failed to connect to MySQL: " . $conn->connect_error);
            } else {
                $this->DB = $conn;
            }
        }
    }
}
