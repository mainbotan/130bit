<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Sessions{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function getSessionById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `sessions` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                $output = $query->fetchAll();

                $out_result = [];
                foreach ($output as $row){
                    $out_result[] = [
                        'id' => $row['id'],
                        'account' => $row['account'],
                        'secret' => $row['secret'],
                        'status' => $row['status'],
                        'time' => $row['time']
                    ];
                }
                return $out_result[0];
            }
            return false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function createSession($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `sessions`(`account`, `secret`) VALUES (
                :account,
                :secret
            )");
            $query->execute($data);
            return $this->DBH->lastInsertId();
        }  
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
            return false;
        }
    }
}

