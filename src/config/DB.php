<?php
namespace TicketTracker\Config;

use PDO;
use PDOException;

class DB
{
    private $hostname;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $options;
    public $db;

    public function __construct(array $params) {
        $this->hostname = $params['hostname'];
        $this->port = $params['port'];
        $this->dbname = $params['dbname'];
        $this->username = $params['username'];
        $this->password = $params['password'];
        $this->options = $params['options'];
        $this->createInstance();
    }

    private function createInstance(): void {
        try {
            $this->db = new PDO(  "mysql:host={$this->hostname};port={$this->port};dbname={$this->dbname}",
                $this->username,
                $this->password,
                $this->options);
        } catch (PDOException $error) {
            throw new PDOException("Error while connecting to DB: " . $error->getMessage());
        }
    }
}