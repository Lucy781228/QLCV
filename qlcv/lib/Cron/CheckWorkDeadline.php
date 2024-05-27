<?php
namespace OCA\QLCV\Cron;

use OCA\QLCV\Service\WorkService;
use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Notification\IManager as NotificationManager;
use OCA\QLCV\Notification\NotificationHelper;
use OCP\ILogger;

class CheckWorkDeadline extends TimedJob {

    private $workService;
    private $notificationHelper;

    private NotificationManager $notificationManager;
    protected $logger;

    public function __construct(ITimeFactory $time, WorkService $workService, NotificationManager $notificationManager, ILogger $logger) {
        parent::__construct($time);
        $this->workService = $workService;
        $this->notificationHelper = new NotificationHelper(
            $notificationManager
        );
        $this->logger = $logger;

        $this->setInterval(360);
    }

    protected function run($arguments) {
        $this->logger->debug('CheckWorkDeadline job is running.', ['app' => 'QLCV']);
        $works = $this->workService->getAllWorks();

        foreach ($works as $work) {
            $daysToDeadline = $this->workService->calculateDaysToDeadline($work['work_id']);

            if ($daysToDeadline === 0) {
                $this->notificationHelper->notifyDueWork($work['assigned_to'], $work['project_name'], $work['work_name']);
            } elseif ($daysToDeadline === 7) {
                $this->notificationHelper->notify7dayWork($work['assigned_to'], $work['project_name'], $work['work_name']);
            } elseif ($daysToDeadline === 30) {
                $this->notificationHelper->notify30dayWork($work['assigned_to'], $work['project_name'], $work['work_name']);
            }
        }
        $this->logger->debug('CheckWorkDeadline job has finished.', ['app' => 'QLCV']);
    }
}