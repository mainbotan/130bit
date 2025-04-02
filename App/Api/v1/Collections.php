<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Collections{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function createCollection($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `collections`(`owner_id`) VALUES (
                :owner_id
            )");
            $query->execute($data);
            return true;
        }  
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
    function getCollection($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `collections` WHERE `owner_id`=:owner_id");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'access' => $row['access'],
                    'owner_id' => (int) $row['owner_id'],
                    'albums' => json_decode($row['albums'], true),
                    'updated_time' => $row['updated_time'],
                    'time' => $row['time']
                ];
            }
            return $out_result[0];
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function updateItems($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `collections` SET 
                `albums` = :albums
                WHERE `id` = :id
            ");
            $query->execute($data);
            return true;
        }  
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
    function updateAccess($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `collections` SET 
                `access` = :access
                WHERE `id` = :id
            ");
            $query->execute($data);
            return true;
        }  
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
}

