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

class KmaNotificationController extends Controller {
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
     * @param integer $notification_id
     * @param string $user_create
     * @param string $content
     * @param date $time_create
     * @param boolean $is_read
     * @param string $user_received
     */
    public function createKmaNotif($notification_id, $user_create, $content, $time_create, $is_read, $user_received) {
        $query = $this->db->getQueryBuilder();
        $query->insert('kma_notification')
                ->values([
                    'notification_id' => $query->createNamedParameter($notification_id),
                    'user_create' => $query->createNamedParameter($user_create),
                    'content' => $query->createNamedParameter($content),
                    'time_create' => $query->createNamedParameter($time_create),
                    'is_read' => $query->createNamedParameter($is_read),
                    'user_received' => $query->createNamedParameter($user_received),
                ])
                ->execute();
            return new DataResponse(['status' => 'success']);
        
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param string $user_received
     */
    public function getNotif($user_id) {
        $query = $this->db->getQueryBuilder();
        $query->select('*')
            ->from('kma_notification')
            ->where($query->expr()->eq('user_received', $query->createNamedParameter($user_id)));

        $result = $query->execute();
        $data = $result->fetchAll();
        if ($data === false) {
            return new DataResponse([], Http::STATUS_NOT_FOUND);
        }
        return new DataResponse([
            'Ma thong bao' => $data['notification_id'],
            'Nguoi tao' => $data['user_create'],
            'Noi dung' => $data['content'],
            'Thoi gian' => $data['time_create'],
        ]);
    }

}