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

class KmaConnectionController extends Controller {
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
     * @param integer $connection_id
     * @param integer $task_id
     * @param integer $file_id
     */
    public function createKmaConnection($connection_id, $task_id, $file_id) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_connection')
                ->values([
                    'connection_id' => $query->createNamedParameter($connection_id),
                    'task_id' => $query->createNamedParameter($task_id),
                    'file_id' => $query->createNamedParameter($file_id),
                ])
                ->execute();
            return new DataResponse(['status' => 'success']);
        
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function getAllKmaConnections() {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_connection');

        $result = $query->execute();
        $connections = $result->fetchAll();
        return ['connections' => $connections];
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $task_id
     */
    public function getKmaConnectionByTask($task_id) {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_connection')
            ->where($query->expr()->eq('task_id', $query->createNamedParameter($task_id)));

        $result = $query->execute();
        $data = $result->fetchAll();
        if ($data === false) {
            return new DataResponse([], Http::STATUS_NOT_FOUND);
        }
        return new DataResponse([
            'Ma lien ket' => $data['connection_id'],
            'Ma tac vu' => $data['task_id'],
            'Ma file' => $data['file_id'],
        ]);
    }
    

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param integer $connection_id
     */
    public function deleteConnection($connection_id) {
        $query = $this->db->getQueryBuilder();
        $query->delete('kma_connection')
            ->where($query->expr()->eq('connection_id', $query->createNamedParameter($connection_id)))
            ->execute();
        return new DataResponse(['status' => 'success']);
    }

    

}