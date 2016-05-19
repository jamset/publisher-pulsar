<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 03.10.15
 * Time: 15:48
 */
namespace React\PublisherPulsar\Inventory;

use React\FractalBasic\Abstracts\Inventory\BaseReactControlDto;
use Monolog\Logger;

class PublisherPulsarDto extends BaseReactControlDto
{

    /**
     * @var float|int seconds
     */
    protected $pulsationIterationPeriod = 1;

    /**
     * @var int
     */
    protected $subscribersPerIteration;

    /**
     * @var PulsarSocketsParamsDto
     */
    protected $pulsarSocketsParams;

    /**
     * @var string
     */
    protected $replyStackCommandName;

    /**Allow to influence on time, that Pulsar will wait actionResultingPush dto, that contain info about
     * either Pulsar should correct its behavior or not.
     * Should consider possible action timeout.
     * @var int
     */
    protected $performerContainerActionMaxExecutionTime;

    /**Allow to set how long Pulsar will wait preparingDto from performer containers before will init
     * performer imitator, that will continue Pulsar work whatever performers subscribed or not.
     * @var int
     */
    protected $maxWaitReplyStackResult;

    /**
     * @return int seconds
     */
    public function getMaxWaitReplyStackResult()
    {
        return $this->maxWaitReplyStackResult;
    }

    /**
     * @param int $maxWaitReplyStackResult
     */
    public function setMaxWaitReplyStackResult($maxWaitReplyStackResult)
    {
        $this->maxWaitReplyStackResult = $maxWaitReplyStackResult;
    }

    /**
     * @return int
     */
    public function getPerformerContainerActionMaxExecutionTime()
    {
        return $this->performerContainerActionMaxExecutionTime;
    }

    /**
     * @param int $performerContainerActionMaxExecutionTime
     */
    public function setPerformerContainerActionMaxExecutionTime($performerContainerActionMaxExecutionTime)
    {
        $this->performerContainerActionMaxExecutionTime = $performerContainerActionMaxExecutionTime;
    }

    /**
     * @return string
     */
    public function getReplyStackCommandName()
    {
        return $this->replyStackCommandName;
    }

    /**
     * @param string $replyStackCommandName
     */
    public function setReplyStackCommandName($replyStackCommandName)
    {
        $this->replyStackCommandName = $replyStackCommandName;
    }

    /**
     * @return PulsarSocketsParamsDto
     */
    public function getPulsarSocketsParams()
    {
        return $this->pulsarSocketsParams;
    }

    /**
     * @param PulsarSocketsParamsDto $pulsarSocketsParams
     */
    public function setPulsarSocketsParams($pulsarSocketsParams)
    {
        $this->pulsarSocketsParams = $pulsarSocketsParams;
    }

    /**
     * @return int
     */
    public function getPulsationIterationPeriod()
    {
        return $this->pulsationIterationPeriod;
    }

    /**
     * @param int $pulsationIterationPeriod
     */
    public function setPulsationIterationPeriod($pulsationIterationPeriod)
    {
        $this->pulsationIterationPeriod = $pulsationIterationPeriod;
    }

    /**
     * @return int
     */
    public function getSubscribersPerIteration()
    {
        return $this->subscribersPerIteration;
    }

    /**
     * @param int $subscribersPerIteration
     */
    public function setSubscribersPerIteration($subscribersPerIteration)
    {
        $this->subscribersPerIteration = $subscribersPerIteration;
    }


}
