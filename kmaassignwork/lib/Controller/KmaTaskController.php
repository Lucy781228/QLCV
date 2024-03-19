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

class KmaTaskController extends Controller
{
    private $db;

    /** @var IUserSession */
    protected $userSession;
    /** @var IGroupManager|Manager */ // FIXME Requires a method that is not on the interface
    protected $groupManager;

    public function __construct($AppName, IRequest $request, IDBConnection $db, IUserSession $userSession, IGroupManager $groupManager)
    {
        parent::__construct($AppName, $request, $userSession, $groupManager);
        $this->db = $db;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $task_id
     * @param string $task_name
     * @param string $task_description
     * @param integer $status_id
     * @param integer $work_id
     * @param integer $level_id
     * @param date $work_start
     * @param date $work_end
     * @param string $user_create
     * @param string $user_respond
     * @param string $user_support
     * @return JSONResponse
     */
    public function createKmaTask($task_id, $task_name, $task_description, $status_id, $work_id, $level_id, $work_start, $work_end, $user_create, $user_respond, $user_support)
    {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_task_item')
              ->values([
                  'task_id' => $query->createNamedParameter($task_id),
                  'task_name' => $query->createNamedParameter($task_name),
                  'task_description' => $query->createNamedParameter($task_description),
                  'status_id' => $query->createNamedParameter($status_id),
                  'work_id' => $query->createNamedParameter($work_id),
                  'level_id' => $query->createNamedParameter($level_id),
                  'work_start' => $query->createNamedParameter($work_start),
                  'work_end' => $query->createNamedParameter($work_end),
                  'user_create' => $query->createNamedParameter($user_create),
                  'user_respond' => $query->createNamedParameter($user_respond),
                  'user_support' => $query->createNamedParameter($user_support),
              ])
              ->execute();
        return new DataResponse(['status' => 'success']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getAllKmaTask()
    {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
              ->from('kma_task_item');

        $result = $query->execute();
        $tasks = $result->fetchAll();
        return ['tasks' => $tasks];
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $user_id
     */
    public function getTaskByUser($user_id)
    {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
              ->from('kma_task_item')
              ->where(
                  $query->expr()->orX(
                      $query->expr()->eq('user_create', $query->createNamedParameter($user_id)),
                      $query->expr()->eq('user_respond', $query->createNamedParameter($user_id)),
                      $query->expr()->eq('user_support', $query->createNamedParameter($user_id))
                  )
              );

        $result = $query->execute();
        $data = $result->fetchAll();

        if (empty($data)) {
            return new DataResponse([], Http::STATUS_NOT_FOUND);
        }
        return new DataResponse($data);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $task_id
     * @param string $task_name
     * @param string $task_description
     * @param integer $status_id
     * @param integer $work_id
     * @param integer $level_id
     * @param date $work_start
     * @param date $work_end
     * @param string $user_create
     * @param string $user_respond
     * @param string $user_support
     * @return JSONResponse
     */
    public function updateTask($task_id, $task_name = null, $task_description = null, $status_id = null, $work_id = null, $level_id = null, $work_start = null, $work_end = null, $user_create = null, $user_respond = null, $user_support = null)
    {
        $query = $this->db->prepare('UPDATE `oc_kma_task_item` SET 
                                    `task_name` = COALESCE(?, `task_name`), 
                                    `task_description` = COALESCE(?, `task_description`), 
                                    `status_id` = COALESCE(?, `status_id`), 
                                    `work_id` = COALESCE(?, `work_id`), 
                                    `level_id` = COALESCE(?, `level_id`), 
                                    `work_start` = COALESCE(?, `work_start`), 
                                    `work_end` = COALESCE(?, `work_end`), 
                                    `user_create` = COALESCE(?, `user_create`), 
                                    `user_respond` = COALESCE(?, `user_respond`), 
                                    `user_support` = COALESCE(?, `user_support`) 
                                  WHERE `task_id` = ?');
        $query->execute(array(
            $task_name,
            $task_description,
            $status_id,
            $work_id,
            $level_id,
            $work_start,
            $work_end,
            $user_create,
            $user_respond,
            $user_support,
            $task_id
        ));

        return new JSONResponse(['status' => 'success']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $task_id
     */
    public function deleteKmaTask($task_id)
    {
        $query = $this->db->getQueryBuilder();
        $query->delete('kma_task_item')
              ->where($query->expr()->eq('task_id', $query->createNamedParameter($task_id)))
              ->execute();
        return new DataResponse(['status' => 'success']);
    }
}