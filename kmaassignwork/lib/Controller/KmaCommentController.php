<?php
namespace OCA\KmaAssignWork\Controller;

use OCP\IRequest;
use OCP\IDBConnection;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCP\IUserSession;
use OCP\IGroupManager;

class KmaCommentController extends Controller {
    private $db;

    /** @var IUserSession */
	protected $userSession;
    /** @var IGroupManager|Manager */ // FIXME Requires a method that is not on the interface
	protected $groupManager;

    public function __construct($AppName, IRequest $request, IDBConnection $db, IUserSession $userSession, IGroupManager $groupManager) {
        parent::__construct($AppName, $request, $userSession, $groupManager);
        $this->db = $db;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
    }

     /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $comment_id
     * @param integer $task_id
     * @param string $user_create
     * @param longtext $message
     */
    public function createKmaComment($comment_id, $task_id, $user_create, $message) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_comment')
                ->values([
                    'comment_id' => $query->createNamedParameter($comment_id),
                    'task_id' => $query->createNamedParameter($task_id),
                    'user_create' => $query->createNamedParameter($user_create),
                    'message' => $query->createNamedParameter($message),
                ])
                ->execute();
            return new DataResponse(['status' => 'success']);
            
    }

/**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $task_id
     */
    public function getKmaCommentInTask($task_id) {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_comment')
            ->where($query->expr()->eq('task_id', $query->createNamedParameter($task_id)));

        $result = $query->execute();
        $data = $result->fetchAll();
        if ($data === false) {
            return new DataResponse([], Http::STATUS_NOT_FOUND);
        }
        return new DataResponse([
            'Ma binh luan' => $data['comment_id'],
            'Ma tac vu' => $data['task_id'],
            'Nguoi viet' => $data['user_create'],
            'Noi dung' => $data['message'],
        ]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $user_create
     */
    public function getKmaCommentByUser($user_create) {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_comment')
            ->where($query->expr()->eq('user_create', $query->createNamedParameter($user_create)));

        $result = $query->execute();
        $data = $result->fetchAll();
        if ($data === false) {
            return new DataResponse([], Http::STATUS_NOT_FOUND);
        }
        return new DataResponse([
            'Ma binh luan' => $data['comment_id'],
            'Ma tac vu' => $data['task_id'],
            'Nguoi viet' => $data['user_create'],
            'Noi dung' => $data['message'],
        ]);
    }
    
    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $comment_id
     * @param integer $task_id
     * @param string $user_create
     * @param longtext $message
     * @return JSONResponse
     */
    public function updateComment($comment_id, $task_id, $user_create, $message = null) {
        $query = $this->db->prepare('UPDATE `oc_kma_comment` SET `message` = COALESCE(?, `message`),  
                                                                WHERE `comment_id` = ?');
        $query->execute(array($message, $comment_id, $task_id, $user_create));
        return new JSONResponse(array('status' => 'success'));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $comment_id
     */
    public function deleteComment($comment_id) {
        $query = $this->db->getQueryBuilder();
        $query->delete('kma_comment')
            ->where($query->expr()->eq('comment_id', $query->createNamedParameter($comment_id)))
            ->execute();
        return new DataResponse(['status' => 'success']);
    }

    

}