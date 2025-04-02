<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Artists{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }
    function getRandomArtists($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `artists` ORDER BY RAND() LIMIT :limit");
            $query->execute($data);
            $output = $query->fetchAll();
            return $output;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getSeveralArtistsByList($data) {
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
            $query = $this->DBH->prepare("SELECT * FROM `artists` WHERE `id` IN ($placeholders)");
            $query->execute($slicedList);
    
            $output = $query->fetchAll(PDO::FETCH_ASSOC); // Получаем ассоциативный массив
    
            $out_result = [];
            foreach ($output as $row) {
                $out_result[] = [
                    'id' => $row['id'],
                    'popularity' => (int) $row['popularity'],
                    'followers' => (int) $row['followers'],
                    'name' => $row['name'],
                    'picture_small' => $row['picture_small'],
                    'picture_big' => $row['picture_big'],
                    'country' => $row['country'],
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
    function checkArtistById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `artists` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                return true;
            }
            return false;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getArtistById($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `artists` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                $output = $query->fetchAll();

                $out_result = [];
                foreach ($output as $row){
                    $out_result[] = [
                        'id' => $row['id'],
                        'popularity' => (int) $row['popularity'],
                        'followers' => (int) $row['followers'],
                        'name' => $row['name'],
                        'picture_small' => $row['picture_small'],
                        'picture_big' => $row['picture_big'],
                        'country' => $row['country'],
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
    function getArtistByName($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `artists` WHERE `name`=:name");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count == 1){
                $output = $query->fetchAll();

                $out_result = [];
                foreach ($output as $row){
                    $out_result[] = [
                        'id' => $row['id'],
                        'popularity' => (int) $row['popularity'],
                        'followers' => (int) $row['followers'],
                        'name' => $row['name'],
                        'picture_small' => $row['picture_small'],
                        'picture_big' => $row['picture_big'],
                        'country' => $row['country'],
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
    function createArtist($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `artists`(`id`, `name`, `popularity`, `followers`, `picture_small`, `picture_big`) VALUES (
                :id,
                :name,
                :popularity,
                :followers,
                :picture_small,
                :picture_big
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
    function getArtistsByDate($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `artists` ORDER BY `time` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'popularity' => (int) $row['popularity'],
                    'followers' => (int) $row['followers'],
                    'name' => $row['name'],
                    'picture_small' => $row['picture_small'],
                    'picture_big' => $row['picture_big'],
                    'country' => $row['country'],
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
    function getArtistsByRegion($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `artists` WHERE `country`=:country ORDER BY `popularity` DESC, `time` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'popularity' => (int) $row['popularity'],
                    'followers' => (int) $row['followers'],
                    'name' => $row['name'],
                    'picture_small' => $row['picture_small'],
                    'picture_big' => $row['picture_big'],
                    'country' => $row['country'],
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
    function getArtistsByPopularity($data){
        global $DBH;
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `artists` ORDER BY `popularity` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            $output = $query->fetchAll();

            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => $row['id'],
                    'popularity' => (int) $row['popularity'],
                    'followers' => (int) $row['followers'],
                    'name' => $row['name'],
                    'picture_small' => $row['picture_small'],
                    'picture_big' => $row['picture_big'],
                    'country' => $row['country'],
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
}

