<?php
namespace OCA\QLCV\Cron;

use OCP\BackgroundJob\TimedJob;
use OCP\AppFramework\Utility\ITimeFactory;

class TestJob extends TimedJob {



    public function __construct(ITimeFactory $time,) {
        parent::__construct($time);

        $this->setInterval(360);
    }

    protected function run($arguments) {
        
    }
}