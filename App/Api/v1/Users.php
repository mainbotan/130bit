<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException



class Users{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }

    function searchUsersByName($data){
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `users` WHERE LOWER(`name`) LIKE LOWER(:search) ORDER BY `name` LIMIT :limit OFFSET :offset");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'country' => $row['country'],
                    'picture' => $row['picture'],
                    'time' => $row['time']
                ];
            }
            return $out_result;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }

    function checkUser($data){
        global $DBH;
        /*PDO Method*/
        $query = $this->DBH->prepare("SELECT * FROM `users` WHERE `name`=?");
        $query->execute(array($data['name']));
        $count = $query->rowCount();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $db_finger = mc_decrypt($result['finger'], SYSTEM_ENCRYPTION_KEY);

        if ($count == 1){
            if ($data['finger'] == $db_finger){
                $z = [
                    'id' => $result['id'],
                    'name' => $result['name'],
                    'finger' => $result['finger'],
                    'country' => $result['country'],
                    'picture' => $result['picture']
                ];
                return $z;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    function getUserById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `users` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 0){
                return null;
            }
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'country' => $row['country'],
                    'picture' => $row['picture'],
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
    function getUserByName($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `users` WHERE `name`=:name");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'country' => $row['country'],
                    'picture' => $row['picture'],
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
    function getSeveralUsers($data) {
        global $DBH;
    
        // Проверка, что массив 'list' не пуст
        if (empty($data['list'])) {
            return []; // Возвращаем пустой массив, если нет данных
        }
    
        // Перевернем массив ID треков
        $reversedList = array_reverse($data['list']);
    
        // Обрезаем массив с использованием offset и limit
        $slicedList = array_slice($reversedList, $data['offset'], $data['limit']);
    
        // Создаем строку плейсхолдеров для каждого ID
        $placeholders = rtrim(str_repeat('?,', count($slicedList)), ',');
    
        try {
            $query = $this->DBH->prepare("SELECT * FROM `users` WHERE `id` IN ($placeholders)");
            $query->execute($slicedList);
    
            $output = $query->fetchAll(PDO::FETCH_ASSOC); // Получаем ассоциативный массив
    
            $out_result = [];
            foreach ($output as $row) {
                $out_result[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'country' => $row['country'],
                    'picture' => $row['picture'],
                    'time' => $row['time']
                ];
            }
            return array_reverse($out_result);
        } catch (PDOException $e) {
            // Записываем ошибку в файл
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);
            return []; // Возвращаем пустой массив в случае ошибки
        }
    }
}

