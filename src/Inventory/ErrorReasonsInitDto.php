<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 05.10.15
 * Time: 3:37
 */
namespace React\PublisherPulsar\Inventory;

class ErrorReasonsInitDto
{
    /**Set by script, that init specific Pulsar and set in specific performers to inform Pulsar
     * @var string
     */
    protected $slowDownReason;

    /**Set by script, that init specific Pulsar and set in specific performers to inform Pulsar
     * @var array containing ErrorSleepForPeriod objects
     */
    protected $sleepForPeriodReasonsContainer = [];

    /**
     * @return array
     */
    public function getSleepForPeriodReasonsContainer()
    {
        return $this->sleepForPeriodReasonsContainer;
    }

    /**
     * @param array $sleepForPeriodReasonsContainer
     */
    public function setSleepForPeriodReasonsContainer($sleepForPeriodReasonsContainer)
    {
        $this->sleepForPeriodReasonsContainer = $sleepForPeriodReasonsContainer;
    }

    /**
     * @return string
     */
    public function getSlowDownReason()
    {
        return $this->slowDownReason;
    }

    /**
     * @param string $slowDownReason
     */
    public function setSlowDownReason($slowDownReason)
    {
        $this->slowDownReason = $slowDownReason;
    }


}
