<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 05.10.15
 * Time: 0:14
 */
namespace React\PublisherPulsar\Inventory;

use React\PublisherPulsar\Abstracts\PushDto;

class ActionResultingPushDto extends PushDto
{
    /**
     * @var bool
     */
    protected $actionCompleteCorrectly;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var string
     */
    protected $errorReason;

    /**
     * @var ErrorSleepForPeriod
     */
    protected $sleepForPeriod;

    /**
     * @var bool
     */
    protected $slowDown;

    /**
     * @return boolean
     */
    public function isActionCompleteCorrectly()
    {
        return $this->actionCompleteCorrectly;
    }

    /**
     * @param boolean $actionCompleteCorrectly
     */
    public function setActionCompleteCorrectly($actionCompleteCorrectly)
    {
        $this->actionCompleteCorrectly = $actionCompleteCorrectly;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return string
     */
    public function getErrorReason()
    {
        return $this->errorReason;
    }

    /**
     * @param string $errorReason
     */
    public function setErrorReason($errorReason)
    {
        $this->errorReason = $errorReason;
    }

    /**
     * @return ErrorSleepForPeriod
     */
    public function getSleepForPeriod()
    {
        return $this->sleepForPeriod;
    }

    /**
     * @param ErrorSleepForPeriod $sleepForPeriod
     */
    public function setSleepForPeriod($sleepForPeriod)
    {
        $this->sleepForPeriod = $sleepForPeriod;
    }

    /**
     * @return boolean
     */
    public function isSlowDown()
    {
        return $this->slowDown;
    }

    /**
     * @param boolean $slowDown
     */
    public function setSlowDown($slowDown)
    {
        $this->slowDown = $slowDown;
    }


}