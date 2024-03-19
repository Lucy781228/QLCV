<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\KmaAssignWork\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
        # Page
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#do_echo', 'url' => '/echo', 'verb' => 'POST'],

       #Work_Item
    //    ['name' => 'KmaWork#sayHi', 'url' => '/sayhi', 'verb' => 'GET'],
       ['name' => 'KmaWork#createKmaWork', 'url' => '/create_kma_work', 'verb' => 'POST'],
       ['name' => 'KmaWork#getUsers', 'url' => '/accounts', 'verb' => 'GET'],
       ['name' => 'KmaWork#getKmaWork', 'url' => '/kma_work/{work_id}', 'verb' => 'GET'],
       ['name' => 'KmaWork#getWorkByUser', 'url' => '/kma_work_by_user/{user_id}', 'verb' => 'GET'],
       ['name' => 'KmaWork#updateWork', 'url' => '/update_kma_work/{work_id}', 'verb' => 'PUT'],
       ['name' => 'KMAUser#deleteKmaWork', 'url' => '/delete_kma_work/{work_id}', 'verb' => 'DELETE'],

       #Task_Item
    //    ['name' => 'KmaTask#sayHi', 'url' => '/sayhii', 'verb' => 'GET'],
       ['name' => 'KmaTask#createKmaTask', 'url' => '/create_kma_task', 'verb' => 'POST'],
       ['name' => 'KmaTask#getTaskByUser', 'url' => '/kma_task/{user_id}', 'verb' => 'GET'],
       ['name' => 'KmaTask#deleteKmaTask', 'url' => '/delete_kma_task/{task_id}', 'verb' => 'DELETE'],
       ['name' => 'KmaTask#updateTask', 'url' => '/update_kma_task/{task_id}', 'verb' => 'PUT'],

       #Level
       ['name' => 'KmaLevel#createKmaLevel', 'url' => '/create_kma_level', 'verb' => 'POST'],
       ['name' => 'KmaLevel#getAllKmaLevel', 'url' => '/all_kma_level', 'verb' => 'GET'],

       #Status
       ['name' => 'KmaStatus#createKmaStatus', 'url' => '/create_kma_status', 'verb' => 'POST'],
       ['name' => 'KmaStatus#getAllKmaStatus', 'url' => '/all_kma_status', 'verb' => 'GET'],

       #Comment
      //  ['name' => 'KmaComment#getAllTaskComments', 'url' => '/all_task_comments', 'verb' => 'GET'],
       ['name' => 'KmaComment#getKmaCommentInTask', 'url' => '/kma_comment/{task_id}', 'verb' => 'GET'],
      //  ['name' => 'KmaComment#getKmaCommentByUser', 'url' => '/kma_comments/{user_create}', 'verb' => 'GET'],
       ['name' => 'KmaComment#createKmaComment', 'url' => '/create_kma_comment', 'verb' => 'POST'],
       ['name' => 'KmaComment#updateComment', 'url' => '/update_comment/{comment_id}', 'verb' => 'PUT'],
       ['name' => 'KmaComment#deleteComment', 'url' => '/delete_comment/{comment_id}', 'verb' => 'DELETE'],


       #Connection
      //  ['name' => 'KmaConnection#getAllKmaConnections', 'url' => '/all_connections', 'verb' => 'GET'],
       ['name' => 'KmaConnection#getKmaConnectionByTask', 'url' => '/kma_connection/{task_id}', 'verb' => 'GET'],
       ['name' => 'KmaConnection#createKmaConnection', 'url' => '/create_kma_connection', 'verb' => 'POST'],
       ['name' => 'KmaConnection#deleteConnection', 'url' => '/delete_connection/{connection_id}', 'verb' => 'DELETE'],


       #Notification
       ['name' => 'KmaNotification#createKmaNotif', 'url' => '/create_kma_notification', 'verb' => 'POST'],
       ['name' => 'KmaNotification#getNotif', 'url' => '/kma_notification/{user_id}', 'verb' => 'GET'],

    ]
];