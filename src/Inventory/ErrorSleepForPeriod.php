<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 16.10.15
 * Time: 3:45
 */
namespace React\PublisherPulsar\Inventory;

class ErrorSleepForPeriod
{
    /**
     * @var int microseconds
     */
    protected $sleepPeriod;

    /**
     * @return int
     */
    public function getSleepPeriod()
    {
        return $this->sleepPeriod;
    }

    /**
     * @param int $sleepPeriod
     */
    public function setSleepPeriod($sleepPeriod)
    {
        $this->sleepPeriod = $sleepPeriod;
    }


}