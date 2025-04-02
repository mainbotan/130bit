<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Subscriptions{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function getSubscriptions($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `subscriptions` WHERE `owner_id`=:owner_id");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'access' => $row['access'],
                    'owner_id' => (int) $row['owner_id'],
                    'artists' => json_decode($row['artists'], true),
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
            $query = $this->DBH->prepare("UPDATE `subscriptions` SET 
                `artists` = :artists
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
    function createSubscriptions($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `subscriptions`(`owner_id`) VALUES (
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
    function updateAccess($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("UPDATE `subscriptions` SET 
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

