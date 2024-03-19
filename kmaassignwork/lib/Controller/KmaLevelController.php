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

class KmaLevelController extends Controller {
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
     * @param integer $level_id
     * @param integer $level_name
     * @param string $level_description
     */
    public function createKmaLevel($level_id, $level_name, $level_description) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_level')
                ->values([
                    'level_id' => $query->createNamedParameter($level_id),
                    'level_name' => $query->createNamedParameter($level_name),
                    'level_description' => $query->createNamedParameter($level_description),
                ])
                ->execute();
            return new DataResponse(['status' => 'success']);
            
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getAllKmaLevel() {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_level');

        $result = $query->execute();
        $tasks = $result->fetchAll();
        return ['levels' => $tasks];
    }

}