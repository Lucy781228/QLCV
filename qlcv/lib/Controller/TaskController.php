<?php
declare(strict_types = 1);
// SPDX-FileCopyrightText: Lucy <ct040407@actv.edu.vn>
// SPDX-License-Identifier: AGPL-3.0-or-later
namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\IDBConnection;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\OCS\OCSNotFoundException;

use OCP\IUserSession;
use OCP\IGroupManager;

use OCA\QLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;


class TaskController extends Controller
{
    private $db;

    protected $userSession;

    protected $groupManager;

    private $notificationHelper;

    public function __construct($AppName, IRequest $request, IDBConnection $db, IUserSession $userSession, IGroupManager $groupManager, NotificationManager $notificationManager)
    {
        parent::__construct($AppName, $request, $userSession, $groupManager);
        $this->db = $db;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->notificationHelper = new NotificationHelper($notificationManager);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getCurrentUsername()
    {
        if ($this
            ->userSession
            ->isLoggedIn())
        {
            $user = $this
                ->userSession
                ->getUser();
            $username = $user->getUID();
            return $username;
        }
        else
        {
            throw new OCSNotFoundException('No user is currently logged in.');
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getUsers()
    {
        $userManager = \OC::$server->getUserManager();
        $users = $userManager->search("");
        $userList = array_map(function ($user)
        {
            return ["user_id" => $user->getUID() , "display_name" => $user->getDisplayName() , ];
        }
        , $users);

        return new JSONResponse(array_values($userList));
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createTask($id, $name, $list_id, $work_id)
    {
        $user_create = $this->getCurrentUsername();
        $query = $this
            ->db
            ->getQueryBuilder();
        $query->insert("qlcv_task")
            ->values(["id" => $query->createNamedParameter($id) , "name" => $query->createNamedParameter($name) , "list_id" => $query->createNamedParameter($list_id) , "work_id" => $query->createNamedParameter($work_id) , "user_create" => $query->createNamedParameter($user_create) , ])->execute();
            $this->notificationHelper->notifyNewTaskRespond($this->getCurrentUsername(), $name);
        return new JSONResponse(["status" => "success"]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getTask($work_id)
    {
        $user_id = $this->getCurrentUsername();
        $query = $this
            ->db
            ->getQueryBuilder();
        $orCondition = $query->expr()
            ->orX($query->expr()
            ->eq("user_create", $query->createNamedParameter($user_id)) , $query->expr()
            ->eq("user_support", $query->createNamedParameter($user_id)) , $query->expr()
            ->eq("user_respond", $query->createNamedParameter($user_id)));

        $workCondition = $query->expr()
            ->eq("work_id", $query->createNamedParameter($work_id));
        $query->select("*")
            ->from("qlcv_task")
            ->where($query->expr()
            ->andX($orCondition, $workCondition));

        $result = $query->execute();
        $data = $result->fetchAll();

        return ['tasks' => $data];
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getTaskById($id)
    {
        $query = $this
            ->db
            ->getQueryBuilder();

        $query->select("*")
            ->from("qlcv_task")
            ->where($query->expr()
            ->eq("id", $query->createNamedParameter($id)));

        $result = $query->execute();
        $data = $result->fetchAll();

        return ['tasks' => $data];
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function updateTask($id, $name, $description, $label, $startDate, $endDate, $listID, $workID, $userRespond, $userSupport)
    {
        $query = $this
            ->db
            ->prepare('UPDATE `oc_qlcv_task` SET 
            `name` = COALESCE(?, `name`), 
            `description` = COALESCE(?, `description`),
            `label` = COALESCE(?, `label`),
            `start_date` = COALESCE(?, `start_date`),
            `end_date` = COALESCE(?, `end_date`),
            `list_id` = COALESCE(?, `list_id`),
            `work_id` = COALESCE(?, `work_id`),
            `user_respond` = COALESCE(?, `user_respond`),
            `user_support` = COALESCE(?, `user_support`)
            WHERE `id` = ?');
        $query->execute([$name, $description, $label, $startDate, $endDate,  $listID, $workID, $userRespond, $userSupport, $id]);

        return new JSONResponse(["status" => "success"]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $id
     * @return JSONResponse
     */
    public function deleteTask($id)
    {
        $query = $this
            ->db
            ->getQueryBuilder();
        $query->delete("qlcv_task")
            ->where($query->expr()
            ->eq("id", $query->createNamedParameter($id)))->execute();

        return new JSONResponse(["status" => "success"]);
    }
}

