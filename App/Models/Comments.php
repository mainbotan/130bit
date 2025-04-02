<?

namespace App\Models;

use App\Api\v1\Comments as CommentsApi;

class Comments{
    private $commentsApi;
    public function __construct(
        CommentsApi $commentsApi
    ){
        $this->commentsApi = $commentsApi;
    }
    public function getItemComments(array $object, int $limit, int $offset){
        $result = $this->commentsApi->getObjectComments([
            'object_id' => $object['id'],
            'object_type' => $object['type'],
            'limit' => $limit,
            'offset' => $offset
        ]);
        return $result;
    }

    public function sendMessage(array $message, array $autho_data){
        if (!$autho_data){ return false; }
        if (!$this->validateMessage($message)){ return false; } 
        $message['value'] = trim(preg_replace('/\s+/', ' ', $message['value']));

        // Чекаем последний коммент объекта
        if (!$this->checkLastComment($message, $autho_data)){ return false; }

        if (!$this->checkOnlyObjectLimit($autho_data['id'], $message['object'])){ return false; }
        if (!$this->checkGlobalLimit($autho_data['id'])){ return false; }
        if (!$this->checkParent($message['parent'])){ return false; }
        if ($message['parent'] == 0){ $message['parent'] = null; }
        if (!$this->commentsApi->createComment([
            'text' => (string) $message['value'],
            'parent' => $message['parent'],
            'object_id' => (string) $message['object']['id'],
            'object_type' => (string) $message['object']['type'],
            'creator' => (int) $autho_data['id'],
        ])){ return false; }
        return true;
    }
    private function checkLastComment(array $message, array $autho_data){
        $last_comment = $this->commentsApi->getLastCommentByObject([
            'object_id' => $message['object']['id'],
            'object_type' => $message['object']['type']
        ]);
        if ((int) $last_comment['creator'] == (int) $autho_data['id']){
            return false;
        }
        return true;
    }
    private function checkParent($parent_id){
        if ($parent_id == null){ return true; }
        if (!$this->commentsApi->checkCommentById(['id' => $parent_id])){ return false; }
        return true;
    }
    private function checkGlobalLimit(int $user_id){ 
        $limit = 10000;
        $count = $this->commentsApi->countAllCreatorComments([
            'creator' => $user_id,
            'offset' => 0,
            'limit' => $limit
        ]);
        if ($count == $limit){ return false; }
        return true;
    }
    private function checkOnlyObjectLimit(int $user_id, array $object){ 
        $limit = 100;
        $count = $this->commentsApi->countCreatorCommentsForObject([
            'object_id' => $object['id'],
            'object_type' => $object['type'],
            'creator' => $user_id,
            'offset' => 0,
            'limit' => $limit
        ]);
        if ($count == $limit){ return false; }
        return true;
    }
    private function validateMessage(array $message): bool {
        // Проверка длины сообщения (с пробелами)
        if (mb_strlen($message['value']) < 3 || mb_strlen($message['value']) > 128) {
            return false;
        }
        
        // Проверка длины сообщения без пробелов (минимум 1 символ после удаления пробелов)
        $messageValueWithoutSpaces = preg_replace('/\s+/', '', $message['value']);
        if (mb_strlen($messageValueWithoutSpaces) < 3) {
            return false;
        }
        
        // Проверка длины ID объекта
        if (mb_strlen($message['object']['id']) < 3 || mb_strlen($message['object']['id']) > 128) {
            return false;
        }
        
        // Проверка допустимого типа объекта
        $types = ['album', 'track', 'user', 'artist', 'playlist'];
        if (!in_array($message['object']['type'], $types)) {
            return false;
        }
        
        return true;
    }
}