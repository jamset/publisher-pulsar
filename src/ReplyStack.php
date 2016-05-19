<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 15.10.15
 * Time: 3:31
 */
namespace React\PublisherPulsar;

use React\FractalBasic\Abstracts\BaseSubsidiary;
use React\FractalBasic\Inventory\ErrorsConstants;
use React\FractalBasic\Inventory\EventsConstants;
use React\FractalBasic\Inventory\InitStartMethodDto;
use React\PublisherPulsar\Inventory\PreparingRequestDto;
use React\PublisherPulsar\Inventory\PulsarErrorConstants;
use React\PublisherPulsar\Inventory\PulsarToReplyStackReplyDto;
use React\PublisherPulsar\Inventory\ReplyStackErrorDto;
use React\PublisherPulsar\Inventory\ReplyStackDto;
use React\PublisherPulsar\Inventory\ReplyStackToPulsarGetTaskRequestDto;
use React\PublisherPulsar\Inventory\ReplyStackToPulsarReturnResultRequestDto;
use Monolog\Logger;
use React\EventLoop\Factory;

class ReplyStack extends BaseSubsidiary
{
    /**
     * @var \ZMQSocket
     */
    protected $pulsarRequestSocket;

    /**
     * @var \ZMQSocket
     */
    protected $performersReplySocket;

    /**
     * @var \ZMQContext
     */
    protected $context;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $loggerPostfix;

    public function startCommunication()
    {
        $this->initLoop();

        $this->context = new \ZMQContext();
        $this->pulsarRequestSocket = $this->context->getSocket(\ZMQ::SOCKET_REQ);
        $this->performersReplySocket = $this->context->getSocket(\ZMQ::SOCKET_REP);

        $this->initStreams();

        $replyStackErrorDtoAlreadySent = false;

        /**
         * Receive sockets params from Pulsar and start cyclical communication
         */
        $this->readStream->on(EventsConstants::DATA, function ($data) use ($replyStackErrorDtoAlreadySent) {

            $replyStackDto = null;
            $replyStackDto = @unserialize($data);

            if (($replyStackDto !== false) && ($replyStackDto instanceof ReplyStackDto)) {

                $this->pulsarRequestSocket->connect($replyStackDto->getReplyStackVsPulsarSocketAddress());
                $this->performersReplySocket->bind($replyStackDto->getReplyStackVsPerformersSocketAddress());

                $this->reactControlDto = $replyStackDto;
                $initDto = new InitStartMethodDto();
                $initDto->setShutDownArg('warning');
                $this->initStartMethods($initDto);

                $this->logger->debug("ReplyStack receive initDto from Pulsar." . $this->loggerPostfix);
                $this->loop->nextTick([$this, 'startStackWork']);

            } else {

                if ($replyStackErrorDtoAlreadySent === false) {

                    $replyStackErrorDtoAlreadySent = true;

                    $replyStackError = new ReplyStackErrorDto();
                    $replyStackError->setErrorLevel(ErrorsConstants::CRITICAL);
                    $replyStackError->setErrorReason(PulsarErrorConstants::REPLY_STACK_RECEIVE_NOT_CORRECT_DTO);

                    //write to Pulsar's allotted STDIN about critical error
                    $this->writeStream->write(serialize($replyStackError));

                    $this->loop->nextTick(function () {
                        $this->loop->stop();
                    });

                }
            }
        });

        $this->loop->run();

        return null;
    }

    public function startStackWork()
    {
        $getTaskDto = new ReplyStackToPulsarGetTaskRequestDto();
        $considerMeAsSubscriber = 0;

        while (true) {

            $this->logger->debug("Start ReplyStack while." . $this->loggerPostfix);

            $this->pulsarRequestSocket->send(serialize($getTaskDto));

            /**Blocking wait reply from Pulsar
             * @var PulsarToReplyStackReplyDto $pulsarToReplyStackReplyDto
             */
            $pulsarToReplyStackReplyDto = unserialize($this->pulsarRequestSocket->recv());

            $this->logger->debug("REPLY STACK asked to prepare subscribers: " . $pulsarToReplyStackReplyDto->getSubscribersNumber()
                . $this->loggerPostfix);

            for ($i = 1; $i <= $pulsarToReplyStackReplyDto->getSubscribersNumber(); $i++) {

                $preparingDto = unserialize($this->performersReplySocket->recv());
                $this->logger->debug("REPLY STACK: receive request $i" . $this->loggerPostfix);

                if ($preparingDto instanceof PreparingRequestDto) {
                    $considerMeAsSubscriber++;
                    $this->logger->debug("REPLY STACK: considerMeAsSubscriber: $considerMeAsSubscriber"
                        . $this->loggerPostfix);
                    $this->performersReplySocket->send(serialize($pulsarToReplyStackReplyDto->getDtoToTransfer()));
                }
            }

            $this->logger->debug("REPLY STACK prepared subscribers: " . $considerMeAsSubscriber
                . $this->loggerPostfix);

            $replyStackResult = new ReplyStackToPulsarReturnResultRequestDto();
            $replyStackResult->setConsiderMeAsSubscriber($considerMeAsSubscriber);

            $this->pulsarRequestSocket->send(serialize($replyStackResult));

            $this->logger->debug("Wait finishing message from Pulsar."
                . $this->loggerPostfix);
            $this->pulsarRequestSocket->recv();
            $this->logger->debug("Got finish message from Pulsar."
                . $this->loggerPostfix);
            $considerMeAsSubscriber = 0;

            $this->logger->debug("Finish ReplyStack while." . $this->loggerPostfix);
        }

        return null;
    }

}
