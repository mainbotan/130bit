<?php

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Account {
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    public function getUserById(int $id) {
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `users` WHERE `id`=?");
            $query->execute([$id]);
            $count = $query->rowCount();
            $output = $query->fetchAll();
            return $output[0] ?? false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    public function updateCurrentPlayer(int $account_id, $currentPlayer) {
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `users` SET `currentPlayer`=? WHERE `id`=?");
            $query->execute([$currentPlayer, $account_id]);
            return true;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
    public function updateName(string $name, int $id) {
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `users` SET `name`=? WHERE `id`=?");
            $query->execute([$name, $id]);
            return true;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
}