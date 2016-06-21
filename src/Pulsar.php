<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 04.10.15
 * Time: 14:49
 */
namespace React\PublisherPulsar;

use React\FractalBasic\Abstracts\BaseReactControl;
use React\FractalBasic\Interfaces\ReactManager;
use React\FractalBasic\Inventory\EventsConstants;
use React\FractalBasic\Inventory\InitStartMethodDto;
use React\FractalBasic\Inventory\LoggingExceptions;
use React\PublisherPulsar\Abstracts\PushDto;
use React\PublisherPulsar\Inventory\ActionResultingPushDto;
use React\PublisherPulsar\Inventory\BecomeTheSubscriberReplyDto;
use React\PublisherPulsar\Inventory\Exceptions\PublisherPulsarException;
use React\PublisherPulsar\Inventory\Exceptions\PublisherPulsarExceptionsConstants;
use React\PublisherPulsar\Inventory\PerformerConstants;
use React\PublisherPulsar\Inventory\PerformerDto;
use React\PublisherPulsar\Inventory\PerformerEarlyTerminated;
use React\PublisherPulsar\Inventory\PerformerSocketsParamsDto;
use React\PublisherPulsar\Inventory\PublisherPulsarDto;
use React\PublisherPulsar\Inventory\PublisherToSubscribersDto;
use React\PublisherPulsar\Inventory\PulsarIterationFinish;
use React\PublisherPulsar\Inventory\PulsarSocketsParamsDto;
use React\PublisherPulsar\Inventory\PulsarToReplyStackReplyDto;
use React\PublisherPulsar\Inventory\ReadyToGetSubscriptionMsg;
use React\PublisherPulsar\Inventory\ReplyStackDto;
use React\PublisherPulsar\Inventory\ReplyStackToPulsarReturnResultRequestDto;
use React\ChildProcess\Process;
use React\ZMQ\Context;
use React\ZMQ\SocketWrapper;

class Pulsar extends BaseReactControl implements ReactManager
{
    /**
     * @var PublisherPulsarDto
     */
    protected $publisherPulsarDto;

    /**
     * @var \ZMQSocket
     */
    protected $publisher;

    /**
     * @var \ZMQSocket
     */
    protected $pullActionInfo;

    /**
     * @var \ZMQSocket
     */
    protected $replyToReplyStack;

    /**
     * @var bool
     */
    protected $slowDown;

    /**
     * @var int microseconds
     */
    protected $sleepDueToSlowDown = 0;

    /**
     * @var int microseconds
     */
    protected $sleepDueToSlowDownChangeStep = 1000000;

    /**
     * @var int microseconds
     */
    protected $maximumSleepDueToSlowDown = 300000000;

    /**
     * @var PulsarSocketsParamsDto
     */
    protected $pulsarSocketsParams;

    /**
     * @var int
     */
    protected $iAmSubscriber = 0;

    /**
     * @var int
     */
    protected $considerMeAsSubscriber = 0;

    /**
     * @var int
     */
    protected $doNotConsiderMeAsSubscriber = 0;

    /**
     * @var int
     */
    protected $shouldBeSubscribersNumber = 0;

    /**
     * @var int
     */
    protected $readyToGetSubscriptionMessage = 0;

    /**
     * @var array
     */
    protected $resultingPushMessages = [];

    /**
     * @var SocketWrapper
     */
    protected $context;

    /**
     * @var bool
     */
    protected $replyStackReturnResult = false;

    /**
     * @var int seconds
     */
    protected $startAwaitBeReadyToAct = 0;

    /**
     * @var int seconds
     */
    protected $maxWaitBeforeHandlePushMessages;

    /**
     * @var int seconds
     */
    protected $maxWaitAllSubscribersReadyBeforePublish = 10;

    /**
     * @var int seconds
     */
    protected $performerContainerActionMaxExecutionTime = 5;

    /**
     * @var bool
     */
    protected $actionResultingContainPerformerError = false;

    /**
     * @var Process
     */
    protected $replyStackProcess;

    /**
     * @var bool
     */
    protected $getRequestForStartFromReplyStack = false;

    /**
     * @var bool
     */
    protected $publishWasMade = false;

    /**
     * @var int microseconds
     */
    protected $sleepForPeriod = 0;

    /**
     * @var int seconds
     */
    protected $maxWaitReplyStackResult = 5;

    /**
     * @var bool
     */
    protected $performerImitatorActive = false;

    /**
     * @var Performer
     */
    protected $performerImitator;

    /**
     * @var int
     */
    protected $performerImitationRequests = 0;

    /**
     * @var int
     */
    protected $maxPerformerImitatorRequests = 20;

    /**
     * @return null
     * @throws PublisherPulsarException
     */
    public function manage()
    {
        if (!($this->publisherPulsarDto instanceof PublisherPulsarDto)) {
            throw new PublisherPulsarException("PublisherPulsarDto wasn't set.");
        }

        $this->reactControlDto = $this->publisherPulsarDto;

        $initDto = new InitStartMethodDto();
        $initDto->setShutDownArg('warning');
        $this->initStartMethods($initDto);

        $this->context = new Context($this->loop);
        $this->shouldBeSubscribersNumber = $this->publisherPulsarDto->getSubscribersPerIteration();
        $this->maxWaitReplyStackResult = ($this->publisherPulsarDto->getMaxWaitReplyStackResult()) ?: $this->maxWaitReplyStackResult;

        if (is_null($this->shouldBeSubscribersNumber)) {
            throw new PublisherPulsarException("Subscribers per iteration number wasn't set.");
        }

        $this->pulsarSocketsParams = $this->publisherPulsarDto->getPulsarSocketsParams();

        $this->initResultingPushMessagesWaiting();
        $this->initSockets();
        $this->initReplyStackProcess();
        $this->declareReplyToReplyStackMessaging();
        $this->declarePushMessaging();

        $this->initPulsar();

        $this->loop->run();

        return null;
    }

    /**
     * @return null
     * @throws PublisherPulsarException
     */
    protected function initResultingPushMessagesWaiting()
    {
        if ($this->publisherPulsarDto->getPerformerContainerActionMaxExecutionTime()) {

            if ($this->publisherPulsarDto->getPerformerContainerActionMaxExecutionTime() < $this->performerContainerActionMaxExecutionTime) {
                throw new PublisherPulsarException("Performer container action max execution time too small: "
                    . $this->publisherPulsarDto->getPerformerContainerActionMaxExecutionTime());
            }

            $this->setMaxWaitBeforeHandlePushMessages(
                $this->shouldBeSubscribersNumber, $this->publisherPulsarDto->getPerformerContainerActionMaxExecutionTime()
            );

            $this->performerContainerActionMaxExecutionTime = $this->publisherPulsarDto->getPerformerContainerActionMaxExecutionTime();

        } else {
            $this->setMaxWaitBeforeHandlePushMessages(
                $this->shouldBeSubscribersNumber, $this->performerContainerActionMaxExecutionTime
            );
        }

        return null;
    }

    /**
     * @param $arg1
     * @param $arg2
     * @return null
     */
    protected function setMaxWaitBeforeHandlePushMessages($arg1, $arg2)
    {
        $this->maxWaitBeforeHandlePushMessages = $arg1 * $arg2 / 2;

        return null;
    }

    /**
     * @return null
     */
    protected function initSockets()
    {
        $this->replyToReplyStack = $this->context->getSocket(\ZMQ::SOCKET_REP);
        $this->replyToReplyStack->bind($this->pulsarSocketsParams->getReplyToReplyStackSocketAddress());

        $this->publisher = $this->context->getSocket(\ZMQ::SOCKET_PUB);
        $this->publisher->bind($this->pulsarSocketsParams->getPublishSocketAddress());

        $this->pullActionInfo = $this->context->getSocket(\ZMQ::SOCKET_PULL);
        $this->pullActionInfo->bind($this->pulsarSocketsParams->getPullSocketAddress());

        return null;
    }

    /**
     * @return null
     */
    protected function initReplyStackProcess()
    {
        $this->replyStackProcess = new Process($this->publisherPulsarDto->getReplyStackCommandName());

        $this->replyStackProcess->on(EventsConstants::PROCESS_EXIT, function ($exitCode, $termSignal) {
            $this->logger->warning("Reply stack sub-process exit with code: "
                . serialize($exitCode) . " | and term signal: " . serialize($termSignal)
                . $this->loggerPostfix
            );
        });

        $this->replyStackProcess->start($this->loop);

        $this->logger->debug("Init reply stack process." . $this->loggerPostfix);

        /**
         * $data is serialized ReplyStackErrorDto
         */
        $this->replyStackProcess->stdout->on(EventsConstants::DATA, function ($data) {
            $this->logger->critical($data . $this->loggerPostfix);
            throw new PublisherPulsarException("Error in STDOUT due to initReplyStackProcess. " . $data . $this->loggerPostfix);
        });

        $this->replyStackProcess->stderr->on(EventsConstants::DATA, function ($data) {
            $this->logger->critical($data . $this->loggerPostfix);
            throw new PublisherPulsarException("Error in STDERR due to initReplyStackProcess. " . $data . $this->loggerPostfix);
        });

        $replyStackDto = new ReplyStackDto();

        $replyStackDto->setReplyStackVsPulsarSocketAddress($this->pulsarSocketsParams->getReplyToReplyStackSocketAddress());
        $replyStackDto->setReplyStackVsPerformersSocketAddress($this->pulsarSocketsParams->getReplyStackSocketAddress());
        $replyStackDto->setLogger($this->publisherPulsarDto->getLogger());
        $replyStackDto->setModuleName($this->publisherPulsarDto->getModuleName());

        $this->replyStackProcess->stdin->write(serialize($replyStackDto));

        $this->startAwaitBeReadyToAct = microtime(true);

        return null;
    }

    /**Coordinating work with ReplyStack as sub-process
     * @return null
     */
    protected function declareReplyToReplyStackMessaging()
    {
        $this->replyToReplyStack->on(EventsConstants::MESSAGE, function ($requestDto) {

            if ($this->getRequestForStartFromReplyStack === false) {

                $this->getRequestForStartFromReplyStack = true;

                $startReplyToReplyStack = new PulsarToReplyStackReplyDto();
                $startReplyToReplyStack->setSubscribersNumber($this->shouldBeSubscribersNumber);
                $startReplyToReplyStack->setDtoToTransfer(new BecomeTheSubscriberReplyDto());

                $this->replyToReplyStack->send(serialize($startReplyToReplyStack));

            } else {

                if ($this->replyStackReturnResult === false) {

                    $this->replyStackReturnResult = true;

                    /**
                     * @var ReplyStackToPulsarReturnResultRequestDto $requestDto
                     */
                    $requestDto = unserialize($requestDto);

                    if (!($requestDto instanceof ReplyStackToPulsarReturnResultRequestDto)) {
                        throw new PublisherPulsarException("Unexpected result from ReplyStack.");
                    }

                    $this->considerMeAsSubscriber = $requestDto->getConsiderMeAsSubscriber();

                    $this->logger->debug("PULSAR RECEIVE REPLY STACK RESULT INFO." . $this->loggerPostfix);

                } else {
                    throw new PublisherPulsarException("Get replyStack result (info about subscribers) twice.");
                }
            }
        });

        $this->replyToReplyStack->on(EventsConstants::ERROR, function ($error) {
            $this->logger->debug(LoggingExceptions::getExceptionString($error) . $this->loggerPostfix);
        });

        return null;
    }

    /**
     * @return null
     */
    protected function declarePushMessaging()
    {
        $this->pullActionInfo->on(EventsConstants::MESSAGE, function ($pushDto) {

            $this->resolvePushMessage(unserialize($pushDto));

            $this->logger->debug("Receive push message $pushDto." . $this->loggerPostfix);
        });

        $this->pullActionInfo->on(EventsConstants::ERROR, function ($error) {
            $this->logger->debug(LoggingExceptions::getExceptionString($error) . $this->loggerPostfix);
        });

        return null;
    }

    /**
     * @param PushDto $pushDto
     * @return null
     */
    protected function resolvePushMessage(PushDto $pushDto)
    {
        switch (true) {
            case($pushDto instanceof PerformerEarlyTerminated):
                /**
                 * @var PerformerEarlyTerminated $pushDto
                 */
                if ($pushDto->isStandOnSubscription()) {
                    $this->doNotConsiderMeAsSubscriber++;
                    $this->iAmSubscriber--;
                }
                $this->logger->debug("Increase doNotConsiderMeAsSubscriber: " . $this->doNotConsiderMeAsSubscriber . $this->loggerPostfix);
                $this->logger->debug("Decrease iAmSubscriber: " . $this->iAmSubscriber . $this->loggerPostfix);
                break;
            case($pushDto instanceof ReadyToGetSubscriptionMsg):
                $this->iAmSubscriber++;
                $this->logger->debug("Increase iAmSubscriber: " . $this->iAmSubscriber . $this->loggerPostfix);
                break;
            case($pushDto instanceof ActionResultingPushDto):
                $this->resultingPushMessages[] = $pushDto;
                $this->logger->debug("Get action result push dto. Resulting push msg number: " . count($this->resultingPushMessages) . $this->loggerPostfix);
                break;
        }

        return null;
    }

    /**
     * @return null
     */
    protected function initPulsar()
    {
        $this->loop->addPeriodicTimer($this->publisherPulsarDto->getPulsationIterationPeriod(), function ($timer) {
            if ($this->sleepForPeriod > 0) {
                $this->logger->notice("Sleep for period: " . $this->sleepForPeriod . $this->loggerPostfix);
            }

            if ($this->replyStackReturnResult === true) {
                $this->logger->debug("Check is ready to publish." . $this->loggerPostfix);
                if ($this->checkIsReadyToPublish()) {
                    $this->replyStackReturnResult = false;
                    $this->correctMaxWaitPushMessagingTime();
                    $this->publish();
                    $this->publishWasMade = true;
                } else {
                    $this->logger->debug("Pulsar is not ready to publish." . $this->loggerPostfix);
                }
            } elseif ($this->publishWasMade) {
                $this->logger->debug("Check is ready to handle resulting messages." . $this->loggerPostfix);
                if ($this->checkIsReadyToHandleResultingPushMessages()) {
                    $this->handleResultingPushMessages();
                } else {
                    $this->logger->debug("Pulsar is not ready to handle resultingPushMessages." . $this->loggerPostfix);
                }
            } else {
                if ($this->performerImitatorActive === false) {
                    $this->logger->debug("Performer imitator not active. Check if possible to activate." . $this->loggerPostfix);
                    if ($this->checkWaitTimeExceeded($this->maxWaitReplyStackResult)) {
                        $this->performerImitatorActive = true;
                        $this->logger->debug("Performer imitator change status to activate. Max waitReplyStackResult " .
                            $this->maxWaitReplyStackResult . " seconds exceeded."
                            . $this->loggerPostfix);
                        $this->initPerformerImitator();
                        $this->logger->debug("Performer imitator change status to not active after work was done."
                            . $this->loggerPostfix);
                    }
                }
                $this->logger->debug("Reply stack doesn't return result yet." . $this->loggerPostfix);
            }
        });

        return null;
    }

    /**
     * @return bool
     */
    protected function checkIsReadyToPublish()
    {
        $checkResult = false;

        $checkName = "TO PUBLISH";
        $this->startLogCheckIsReady($checkName);

        if (
        $this->checkEquality(
            $this->shouldBeSubscribersNumber,
            ($this->iAmSubscriber + $this->doNotConsiderMeAsSubscriber + $this->performerImitationRequests))
        ) {
            $this->logger->debug("All considered subscribers ready." . $this->loggerPostfix);
            $checkResult = true;
        } elseif ($this->checkWaitTimeExceeded($this->maxWaitAllSubscribersReadyBeforePublish)) {
            $this->logger->info("FORCE ALLOWING TO PUBLISH.");
            // Responded subscribers number less, than should be (because of unexpected troubles with
            // performers process or because of unexpected push messages handling
            $checkResult = true;
        }

        $this->finishLogCheckIsReady($checkName);

        return $checkResult;
    }

    /**
     * @return null
     */
    protected function correctMaxWaitPushMessagingTime()
    {
        $this->setMaxWaitBeforeHandlePushMessages($this->iAmSubscriber, $this->performerContainerActionMaxExecutionTime);

        return null;
    }

    /**
     * @return null
     */
    protected function publish()
    {
        $this->logger->debug("Come to publish." . $this->loggerPostfix);

        if ($this->sleepDueToSlowDown > 0) {
            $this->logger->notice("Pulsar sleep for microseconds: " . $this->sleepDueToSlowDown . $this->loggerPostfix);
            usleep($this->sleepDueToSlowDown);
            $this->logger->notice("Wake up before publish." . $this->loggerPostfix);
        }

        $this->publisher->send(serialize(new PublisherToSubscribersDto()));

        //to checkWaitTimeExceeded for handlingResultingPushMessages
        $this->startAwaitBeReadyToAct = microtime(true);

        $this->logger->debug("Publish sent." . $this->loggerPostfix);

        return null;
    }

    /**
     * @return bool
     */
    protected function checkIsReadyToHandleResultingPushMessages()
    {
        $checkResult = false;

        $checkName = "TO HANDLE RESULTING";
        $this->startLogCheckIsReady($checkName);
        $this->logger->debug("Max wait before handle pushMessages: " . $this->maxWaitBeforeHandlePushMessages . $this->loggerPostfix);

        if ($this->checkBiggerOrEqual(
            (count($this->resultingPushMessages) + $this->performerImitationRequests), $this->iAmSubscriber)
        ) {
            $checkResult = true;
        } elseif ($this->checkWaitTimeExceeded($this->maxWaitBeforeHandlePushMessages)) {
            $this->logger->info("FORCE ALLOWING HANDLE RESULTING MESSAGES." . $this->loggerPostfix);
            $checkResult = true;
        }

        $this->finishLogCheckIsReady($checkName);

        return $checkResult;
    }

    /**
     * @return null
     */
    protected function handleResultingPushMessages()
    {
        $this->logger->debug("Pulsar start handle resultingPushMessages." . $this->loggerPostfix);
        $this->actionResultingContainPerformerError = false;
        /**
         * @var ActionResultingPushDto $pushMessage
         */
        foreach ($this->resultingPushMessages as $key => $pushMessage) {
            $this->logger->debug("Try to find error." . $this->loggerPostfix);
            $this->logger->debug("PushMessage contain: " . serialize($pushMessage) . $this->loggerPostfix);

            if ($pushMessage->getErrorMessage()) {
                $this->actionResultingContainPerformerError = true;
                $this->logger->info("ERROR OF EXECUTION EXIST." . $this->loggerPostfix);
                $this->logger->info("Error reason: " . $pushMessage->getErrorReason() . $this->loggerPostfix);
                $this->logger->info("Start error handling: " . $pushMessage->getErrorReason() . $this->loggerPostfix);

                $this->handleErrorReason($pushMessage);

                $this->logger->info("Finish error handling: " . $pushMessage->getErrorReason() . $this->loggerPostfix);
                break;
            }
        }

        /* Try to speed up, if was slowed before
         * */
        if ($this->actionResultingContainPerformerError === false) {
            if ($this->sleepDueToSlowDown !== 0) {
                $this->sleepDueToSlowDown -= $this->sleepDueToSlowDownChangeStep;
                $this->logger->info("Pulsar accelerated. Wait interval made smaller: " . $this->sleepDueToSlowDown . $this->loggerPostfix);

                if ($this->sleepDueToSlowDown < 0) {
                    $this->sleepDueToSlowDown = 0;
                    $this->logger->info("Pulsar accelerated. Wait interval set to zero." . $this->loggerPostfix);
                }

            } else {
                if ($this->shouldBeSubscribersNumber < $this->publisherPulsarDto->getSubscribersPerIteration()) {
                    $this->shouldBeSubscribersNumber++;
                    $this->logger->info("Pulsar accelerated. Subscribers number increased: " . $this->shouldBeSubscribersNumber . $this->loggerPostfix);
                } else {
                    $this->logger->info("Subscribers number is equal to expected." . $this->loggerPostfix);
                }
            }
        }

        $this->finishIteration();
        $this->logger->debug("Push messages resolved." . $this->loggerPostfix);
        return null;
    }

    /**
     * @return null
     * @throws PublisherPulsarException
     */
    protected function initPerformerImitator()
    {
        $this->logger->debug("Performer imitator start work." . $this->loggerPostfix);

        if (!$this->performerImitator) {

            $performerDto = new PerformerDto();
            $performerDto->setLogger($this->logger);
            $performerDto->setModuleName(PerformerConstants::PERFORMER_IMITATOR);

            $this->performerImitator = new Performer($performerDto);

            $performerSocketParams = new PerformerSocketsParamsDto();
            $performerSocketParams->setRequestPulsarRsSocketAddress($this->pulsarSocketsParams->getReplyStackSocketAddress());

            $this->performerImitator->setSocketsParams($performerSocketParams);
        }

        $sendStatuses = [];
        $requestsNumber = 0;

        //balance between needed to go further and the activity of real performers
        while ((count($sendStatuses) < ($this->shouldBeSubscribersNumber - $this->iAmSubscriber))
            && ($requestsNumber <
                ($this->shouldBeSubscribersNumber + ($this->shouldBeSubscribersNumber * 0.5)))) {
            sleep(1);
            $sendStatus = $this->performerImitator->requestForActionPermission("don't wait");

            if ($sendStatus) {
                $sendStatuses[] = $sendStatus;
                $this->performerImitationRequests++;
                $this->logger->debug("Performer imitator send success imitation request: " . $this->performerImitationRequests . $this->loggerPostfix);
            }

            $requestsNumber++;
        }

        $this->logger->debug("Performer imitator finish work." . $this->loggerPostfix);
        $this->performerImitatorActive = false;
        //$this->performerImitationRequests = 0;

        return null;
    }

    /**
     * @param $checkName
     * @return null
     */
    protected function startLogCheckIsReady($checkName)
    {
        $this->logger->debug("START __ CHECK READY $checkName." . $this->loggerPostfix);
        $this->logger->debug("Should be subscribers number: " . $this->shouldBeSubscribersNumber . $this->loggerPostfix);
        $this->logger->debug("ConsiderMeAsSubscriber: " . $this->considerMeAsSubscriber . $this->loggerPostfix);
        $this->logger->debug("I am subscriber: " . $this->iAmSubscriber . $this->loggerPostfix);
        $this->logger->debug("Don't consider me as subscriber: " . $this->doNotConsiderMeAsSubscriber . $this->loggerPostfix);
        $this->logger->debug("Sum (iAm and doNotConsider): " . ($this->iAmSubscriber + $this->doNotConsiderMeAsSubscriber) . $this->loggerPostfix);
        $this->logger->debug("Resulting push messages:  " . count($this->resultingPushMessages) . $this->loggerPostfix);
        $this->logger->debug("Performer imitation requests: " . $this->performerImitationRequests . $this->loggerPostfix);

        return null;
    }

    /**
     * @param $checkName
     * @return null
     */
    protected function finishLogCheckIsReady($checkName)
    {
        $this->logger->debug("FINISH CHECK ___ READY $checkName." . $this->loggerPostfix);

        return null;
    }

    /**
     * @param $argToCompare
     * @return bool
     */
    protected function checkWaitTimeExceeded($argToCompare)
    {
        $checkResult = false;

        if ($this->startAwaitBeReadyToAct > 0) {
            $currentMicroTime = microtime(true);

            if (($currentMicroTime - $this->startAwaitBeReadyToAct) > $argToCompare) {
                $checkResult = true;
                $this->startAwaitBeReadyToAct = microtime(true); //for next step checking
            }
        }

        return $checkResult;
    }

    /**
     * @param $arg1
     * @param $arg2
     * @return bool
     */
    protected function checkBiggerOrEqual($arg1, $arg2)
    {
        $checkResult = false;

        if ($arg1 >= $arg2) {
            $checkResult = true;
        }

        return $checkResult;
    }

    /**
     * @param $arg1
     * @param $arg2
     * @return bool
     */
    protected function checkEquality($arg1, $arg2)
    {
        $checkResult = false;

        if ($arg1 === $arg2) {
            $checkResult = true;
        }

        return $checkResult;
    }

    /**
     * @param ActionResultingPushDto $pushDto
     * @return null
     */
    protected function handleErrorReason(ActionResultingPushDto $pushDto)
    {
        switch (true):
            case(is_null($pushDto->isSlowDown()) === false):
                $this->logger->info("Start SLOW DOWN Pulsar | Error reason: " . $pushDto->getErrorReason() .
                    " | Error message: " . $pushDto->getErrorMessage() . $this->loggerPostfix);
                $this->slowDown();
                break;
            case(is_null($pushDto->getSleepForPeriod()) === false):
                $this->sleepForPeriod($pushDto);
                break;
            default:
                $this->logger->error("Error wasn't handled because of unknown error reason. | " . serialize($pushDto) . $this->loggerPostfix);
        endswitch;

        return null;
    }

    /**
     * @return null
     */
    protected function slowDown()
    {
        if ($this->iAmSubscriber > 1) {

            $this->shouldBeSubscribersNumber--;
            $this->logger->info("Pulsar slowed. Subscribers number decreased: " . $this->shouldBeSubscribersNumber . $this->loggerPostfix);

        } else {

            if ($this->sleepDueToSlowDown < $this->maximumSleepDueToSlowDown) {
                $this->sleepDueToSlowDown += $this->sleepDueToSlowDownChangeStep;
                $this->logger->info("Pulsar slowed. Wait interval made bigger: " . $this->sleepDueToSlowDown . $this->loggerPostfix);
            } else {
                $this->logger->info(PublisherPulsarExceptionsConstants::WAITING_INTERVAL_EXCEED_MAXIMUM_VALUE . $this->loggerPostfix);
            }
        }

        return null;
    }

    /**
     * @param ActionResultingPushDto $pushDto
     * @return null
     */
    protected function sleepForPeriod(ActionResultingPushDto $pushDto)
    {
        $this->logSleepForPeriod($pushDto);
        $this->sleepForPeriod = $pushDto->getSleepForPeriod()->getSleepPeriod();

        return null;
    }

    /**
     * @param ActionResultingPushDto $pushDto
     * @return null
     */
    protected function logSleepForPeriod(ActionResultingPushDto $pushDto)
    {
        $this->logger->info("Initiate sleep for period (microseconds): "
            . $pushDto->getSleepForPeriod()->getSleepPeriod()
            . " | Error reason: " . $pushDto->getErrorReason()
            . " | Error message: " . $pushDto->getErrorMessage() . $this->loggerPostfix);

        return null;
    }

    /**
     * @return null
     */
    protected function finishIteration()
    {
        $this->iAmSubscriber = 0;
        $this->considerMeAsSubscriber = 0;
        $this->doNotConsiderMeAsSubscriber = 0;

        $this->replyStackReturnResult = false;
        $this->startAwaitBeReadyToAct = microtime(true);

        $this->getRequestForStartFromReplyStack = false;

        $this->publishWasMade = false;

        $this->resultingPushMessages = [];

        //$this->performerImitatorActive = false;
        $this->performerImitationRequests = 0;

        if ($this->sleepForPeriod > 0) {

            $this->logger->info("Start sleep for (microseconds) " . $this->sleepForPeriod . $this->loggerPostfix);
            usleep($this->sleepForPeriod);
            $this->logger->info("Finish sleep for (microseconds) " . $this->sleepForPeriod . $this->loggerPostfix);

            $this->sleepForPeriod = 0;
        }

        $this->replyToReplyStack->send(serialize(new PulsarIterationFinish()));

        $this->logger->debug("Pulsar finish iteration and set false/zero values to relevant properties." . $this->loggerPostfix);

        return null;
    }

    /**
     * @return PublisherPulsarDto
     */
    public function getPublisherPulsarDto()
    {
        return $this->publisherPulsarDto;
    }

    /**
     * @param $publisherPulsarDto
     */
    public function setPublisherPulsarDto($publisherPulsarDto)
    {
        $this->publisherPulsarDto = $publisherPulsarDto;
    }


}
