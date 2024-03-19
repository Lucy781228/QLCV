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

class KmaStatusController extends Controller {
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
     * @param integer $status_id
     * @param string $status_name
     */
    public function createKmaStatus($status_id, $status_name) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_status')
                ->values([
                    'status_id' => $query->createNamedParameter($status_id),
                    'status_name' => $query->createNamedParameter($status_name),
                ])
                ->execute();
            return new DataResponse(['status' => 'success']);
        
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getAllKmaStatus() {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_status');

        $result = $query->execute();
        $statuses = $result->fetchAll();
        return ['statuses' => $statuses];
    }

}