<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\ProjectService;
use OCA\QLCV\Notification\NotificationHelper;
use OCP\Notification\IManager as NotificationManager;


use OCP\BackgroundJob\IJobList;
class ProjectController extends Controller {
    private $projectService;
    private $notificationHelper;



    private IJobList $jobList;

    public function __construct(
        $AppName,
        IRequest $request,
        ProjectService $projectService,
        NotificationManager $notificationManager,


        IJobList $jobList
    ) {
        parent::__construct($AppName, $request);
        $this->projectService = $projectService;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );


        $this->jobList = $jobList;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function createProject(
        $project_id,
        $project_name,
        $user_id,
        $start_date,
        $end_date
    ) {
        $result = $this->projectService->createProject($project_id, $project_name, $user_id, $start_date, $end_date);
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getProjects($user_id) {
        $this->jobList->add(TestJob::class);


        $data = $this->projectService->getProjects($user_id);
        return new JSONResponse(['projects' => $data]);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function updateProject(
        $project_id,
        $project_name,
        $user_id,
        $start_date,
        $end_date,
        $status
    ) {
        $result = $this->projectService->updateProject($project_id, $project_name, $user_id, $start_date, $end_date, $status);
        // $this->notificationHelper->notifyRenameProject('user1', 'TEST', $project_name);
        return new JSONResponse($result);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function deleteProject($project_id) {
        $result = $this->projectService->deleteProject($project_id);
        return new JSONResponse($result);
    }
}