<?php
namespace TicketTracker\Config;

use PDO;
use PDOException;
use TicketTracker\Helpers\Response;

class DB
{
    private string $hostname;
    private int $port;
    private string $dbname;
    private string $username;
    private string $password;
    private array $options;
    public PDO $db;

    public function __construct(array $params) {
        $this->hostname = $params['hostname'];
        $this->port = $params['port'];
        $this->dbname = $params['dbname'];
        $this->username = $params['username'];
        $this->password = $params['password'];
        $this->options = $params['options'];
        $this->createInstance();
    }

    private function createInstance() {
        try {
            $this->db = new PDO(  "mysql:host=$this->hostname;port=$this->port;dbname=$this->dbname",
                $this->username,
                $this->password,
                $this->options);
            return $this->db;
        } catch (PDOException $error) {
            Response::json(['error' => 'Error while connecting to DB: ' . $error->getMessage()], 500);
            exit;
        }
    }
}