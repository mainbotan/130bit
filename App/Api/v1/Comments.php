<?

namespace App\Api\v1;

use PDO; // Импортируем PDO
use PDOException; // Импортируем PDOException


class Comments{
    private $DBH;
    function __construct($DBH){
        $this->DBH = $DBH;
    }

    function getLastCommentByObject($data){
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `comments` WHERE `object_id`=:object_id AND `object_type`=:object_type ORDER BY `time` DESC LIMIT 1");
            $query->execute($data);
            $output = $query->fetchAll();
            $out_result = [];
            foreach ($output as $row){
                $out_result[] = [
                    'id' => (int) $row['id'],
                    'creator' => $row['creator'],
                    'text' => $row['text']
                ];
            }
            return $out_result[0];
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function getObjectComments($data) {
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
    
            // Выбираем корневые комментарии с пагинацией
            $query = $this->DBH->prepare("
                SELECT * 
                FROM `comments` 
                WHERE `object_id` = :object_id 
                  AND `object_type` = :object_type 
                  AND `parent` IS NULL
                ORDER BY `time` DESC
                LIMIT :limit OFFSET :offset
            ");
    
            $query->bindValue(':object_id', $data['object_id'], PDO::PARAM_INT);
            $query->bindValue(':object_type', $data['object_type'], PDO::PARAM_STR);
            $query->bindValue(':limit', $data['limit'], PDO::PARAM_INT);
            $query->bindValue(':offset', $data['offset'], PDO::PARAM_INT);
    
            $query->execute();
            $rootComments = $query->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($rootComments)) {
                return [];
            }
    
            // Получаем ID всех пользователей, оставивших комментарии
            $userIds = array_unique(array_column($rootComments, 'creator'));
            $allComments = $this->getAllReplies(array_column($rootComments, 'id'), $data['object_id'], $data['object_type']);
            $userIds = array_unique(array_merge($userIds, array_column($allComments, 'creator')));
    
            // Получаем данные пользователей
            $usersData = $this->getUsersData($userIds);
    
            // Строим дерево с подставленными данными пользователей
            $tree = $this->buildCommentTree($allComments, $rootComments, null, 0, $usersData);
            return $tree;
        } catch (PDOException $e) {
            file_put_contents(__DIR__ . '/PDOErrors.txt', $e->getMessage(), FILE_APPEND);
            return [];
        }
    }
    
    /**
     * Получает данные пользователей по их ID
     */
    private function getUsersData(array $userIds) {
        if (empty($userIds)) return [];
    
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $query = $this->DBH->prepare("
            SELECT id, name, picture 
            FROM users 
            WHERE id IN ($placeholders)
        ");
        $query->execute($userIds);
        
        $users = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $user) {
            $users[$user['id']] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'picture' => $user['picture']
            ];
        }
        return $users;
    }
    
    private function getAllReplies(array $rootCommentIds, $objectId, string $objectType) {
        if (empty($rootCommentIds)) return [];
    
        $query = $this->DBH->prepare("
            WITH RECURSIVE CommentTree AS (
                SELECT *
                FROM `comments`
                WHERE `object_id` = :object_id
                  AND `object_type` = :object_type
                  AND `parent` IN (" . implode(',', $rootCommentIds) . ")
                UNION ALL
                SELECT c.*
                FROM `comments` c
                INNER JOIN CommentTree ct ON c.parent = ct.id
            )
            SELECT * FROM CommentTree
            LIMIT 1000
        ");
    
        $query->execute([
            'object_id' => $objectId,
            'object_type' => $objectType
        ]);
    
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function buildCommentTree(array $allComments, array $rootComments, ?int $parentId, int $depth, array $usersData) {
        $branch = [];
    
        foreach ($rootComments as $comment) {
            $commentParent = $comment['parent'] === null ? null : (int) $comment['parent'];
            if ($commentParent === $parentId) {
                $node = [
                    'id' => (int) $comment['id'],
                    'creator' => $usersData[$comment['creator']] ?? null, // Заменяем ID на данные пользователя
                    'text' => $comment['text'],
                    'object_id' => $comment['object_id'],
                    'object_type' => $comment['object_type'],
                    'parent' => $commentParent,
                    'time' => $comment['time'],
                    'replies' => []
                ];
    
                if ($depth < 3) {
                    $node['replies'] = $this->buildCommentTree(
                        $allComments, 
                        $allComments, 
                        $comment['id'], 
                        $depth + 1, 
                        $usersData
                    );
                }
    
                $branch[] = $node;
            }
        }
    
        return $branch;
    }


    function countAllCreatorComments($data){
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `comments` WHERE `creator`=:creator ORDER BY `time` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            return $count;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
    function countCreatorCommentsForObject($data){
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT `id` FROM `comments` WHERE `object_id`=:object_id AND `object_type`=:object_type AND `creator`=:creator ORDER BY `time` DESC LIMIT :offset, :limit");
            $query->execute($data);
            $count = $query->rowCount();
            return $count;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }

    function checkCommentById($data){
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("SELECT * FROM `comments` WHERE `id`=:id");
            $query->execute($data);
            $count = $query->rowCount();
            if ($count != 1){ return false; }
            return true;
        }
        catch(PDOException $e) {  
            $e_url = __DIR__ . '/PDOErrors.txt';
            file_put_contents($e_url, $e->getMessage(), FILE_APPEND);  
        }
    }
 
    function createComment($data){
        try {
            $this->DBH->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $query = $this->DBH->prepare("INSERT INTO `comments`(`text`, `object_id`, `object_type`, `creator`, `parent`) VALUES (
                :text,
                :object_id,
                :object_type,
                :creator,
                :parent
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
}