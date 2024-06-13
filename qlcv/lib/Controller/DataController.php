<?php
declare(strict_types=1);

namespace OCA\QLCV\Controller;

use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCA\QLCV\Service\ProjectAnalystService;
use OCA\QLCV\Service\PredictionService;
use OCA\QLCV\Service\AuthorizationService;
use OCP\IUserSession;

class DataController extends Controller
{
    private $projectAnalystService;
    private $predictionService;
    private $authorizationService;
    private $userSession;

    public function __construct(
        $AppName,
        IRequest $request,
        ProjectAnalystService $projectAnalystService,
        PredictionService $predictionService,
        AuthorizationService $authorizationService,
        IUserSession $userSession
    ) {
        parent::__construct($AppName, $request);
        $this->projectAnalystService = $projectAnalystService;
        $this->predictionService = $predictionService;
        $this->authorizationService = $authorizationService;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function countWorksPerProject()
    {
        $currentUser = $this->userSession->getUser();
        if (!$currentUser) {
            return new JSONResponse(["error" => "User not authenticated"], 403);
        }
        $startDate = $this->request->getParam('startDate');
        $endDate = $this->request->getParam('endDate');
        $data = $this->projectAnalystService->countWorksPerProject($startDate, $endDate, $currentUser->getUID());
        return new JSONResponse(['data' => $data]); 
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function predictWorksCompletionTimes($project_id)
    {
        try {
            $this->authorizationService->isProjectOwner($project_id);
            $data = $this->predictionService->predictWorksCompletionTimes($project_id);
            return new JSONResponse(["data" => $data]);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function simplePredict($work_id)
    {
        try {
            $this->authorizationService->isWorkOwner($work_id);
            $data = $this->predictionService->simplePredict($work_id);
            return new JSONResponse(["data" => $data]);
        } catch (\Exception $e) {
            return new JSONResponse(
                ["error" => $e->getMessage()],
                $e->getCode()
            );
        }
    }
}
