<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 03.10.15
 * Time: 15:48
 */
namespace React\PublisherPulsar\Inventory;

use React\FractalBasic\Abstracts\Inventory\BaseReactDto;
use Monolog\Logger;

class PublisherPulsarDto extends BaseReactDto
{

    /**
     * @var float|int seconds
     */
    protected $pulsationIterationPeriod = 1;

    /**
     * @var int
     */
    protected $subscribersPerIteration = 10;

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

    /**Allowing to make permission to act or to coordinate certain subscribers activity
     * @var PublisherToSubscribersDto
     */
    protected $publisherToSubscribersDto;

    /**
     * @return null
     */
    public function initDefaultPulsarSocketsParams()
    {
        $pulsarSocketsParams = new \React\PublisherPulsar\Inventory\PulsarSocketsParamsDto();

        $pulsarSocketsParams->setReplyToReplyStackSocketAddress('tcp://127.0.0.1:6271');
        $pulsarSocketsParams->setPushToReplyStackSocketAddress('tcp://127.0.0.1:6272');
        $pulsarSocketsParams->setPublishSocketAddress('tcp://127.0.0.1:6273');
        $pulsarSocketsParams->setPullSocketAddress('tcp://127.0.0.1:6274');
        $pulsarSocketsParams->setReplyStackSocketAddress('tcp://127.0.0.1:6275');

        $this->pulsarSocketsParams = $pulsarSocketsParams;

        return null;
    }

    /**
     * @return PublisherToSubscribersDto
     */
    public function getPublisherToSubscribersDto()
    {
        return $this->publisherToSubscribersDto;
    }

    /**
     * @param PublisherToSubscribersDto $publisherToSubscribersDto
     */
    public function setPublisherToSubscribersDto($publisherToSubscribersDto)
    {
        $this->publisherToSubscribersDto = $publisherToSubscribersDto;
    }

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
