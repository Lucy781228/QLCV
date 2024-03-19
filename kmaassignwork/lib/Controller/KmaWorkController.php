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

class KmaWorkController extends Controller {

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
     * CAUTION: the @Stuff turns off security checks; for this page, no admin is
     * required and no CSRF check. If you don't know what CSRF is, read
     * it up in the docs or you might create a security hole. This is
     * basically the only required method to add this exemption, don't
     * add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function sayHi() {
        $message = "It's work, brooooo";
        return new DataResponse($message);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getUsers() {
        $userManager = \OC::$server->getUserManager();
        $users = $userManager->search('');
        $userList = array_map(function ($user) {
            return [
                'user_id' => $user->getUID(),
                'display_name' => $user->getDisplayName(),
            ];
        }, $users);

        return new JSONResponse(array_values($userList));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createKmaWork($work_id, $work_name, $work_description, $level_id, $status_id, $user_create) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_work_item')
            ->values([
                'work_id' => $query->createNamedParameter($work_id),
                'work_name' => $query->createNamedParameter($work_name),
                'work_description' => $query->createNamedParameter($work_description),
                'level_id' => $query->createNamedParameter($level_id),
                'status_id' => $query->createNamedParameter($status_id),
                'user_create' => $query->createNamedParameter($user_create),
            ])
            ->execute();

        // return new DataResponse(['status' => 'success']);
        return new DataResponse(['status' => 'success']);
    }

        /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $user_id
     */
    public function getWorkByUser($user_id)
    {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
              ->from('kma_work_item')
              ->where(
                  $query->expr()->orX(
                      $query->expr()->eq('user_create', $query->createNamedParameter($user_id))
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
 * @param int $work_id
 * @return DataResponse
 */
public function getKmaWork($work_id) {
    $query = $this->db->getQueryBuilder();
    $query->select('*')
        ->from('kma_work_item')
        ->where($query->expr()->eq('work_id', $query->createNamedParameter($work_id)));

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
     * @param integer $work_id
     * @param string $work_name
     * @param longtext $work_description
     * @param integer $level_id
     * @param integer $status_id
     * @param string $user_create
     * @return JSONResponse
     */
    public function updateWork($work_id, $work_name = null, $work_description = null, $level_id = null, $status_id = null, $user_create) {
        $query = $this->db->prepare('UPDATE `oc_kma_work_item` SET `work_name` = COALESCE(?, `work_name`), 
                                                            `work_description` = COALESCE(?, `work_description`), 
                                                            `level_id` = COALESCE(?, `level_id`), 
                                                            `status_id` = COALESCE(?, `status_id`) 
                                                            WHERE `kma_work_id` = ?');
        $query->execute([$work_name, $work_description, $level_id, $status_id, $work_id, $user_create]);

        return new JSONResponse(['status' => 'success']);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $work_id
     * @return DataResponse
     */
    public function deleteKmaWork($work_id) {
        $query = $this->db->getQueryBuilder();
        $query->delete('kma_work_item')
            ->where($query->expr()->eq('work_id', $query->createNamedParameter($work_id)))
            ->execute();

        return new DataResponse(['status' => 'success']);
    }
}
