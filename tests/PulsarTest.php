<?php

/**
 * Created by PhpStorm.
 * User: nms
 * Date: 03.06.16
 * Time: 15:13
 */
use React\PublisherPulsar\Pulsar;

class PulsarTest extends PHPUnit_Framework_TestCase
{

    public function testReceivingResultingPushMessages()
    {
        /*$pulsar = $this->getMockBuilder(Pulsar::class)
        ->setMethods(['handleResultingPushMessages'])
        ->getMock();

        $pulsar->expects($this->once())
            ->method('handleResultingPushMessages')
            ->will($this->returnSelf());

        $pulsar->manage();*/

        $pulsar = new \React\PublisherPulsar\Pulsar();

        $pulsar->setLimitNumbersOfIterations(5);

        $publisherPulsarDto = new \React\PublisherPulsar\Inventory\PublisherPulsarDto();

        $publisherPulsarDto->setPulsationIterationPeriod(1);
        $publisherPulsarDto->setSubscribersPerIteration(10);
        $publisherPulsarDto->setModuleName('react:pulsar');

        $dir = __DIR__;

        $publisherPulsarDto->setReplyStackCommandName("php $dir/ReplyStackCommand.php");

        $publisherPulsarDto->setPerformerContainerActionMaxExecutionTime(7);

        $publisherPulsarDto->setMaxWaitReplyStackResult(7);

        $pulsarSocketsParams = new \React\PublisherPulsar\Inventory\PulsarSocketsParamsDto();

        $pulsarSocketsParams->setReplyToReplyStackSocketAddress('tcp://127.0.0.1:6261');
        $pulsarSocketsParams->setPushToReplyStackSocketAddress('tcp://127.0.0.1:6262');
        $pulsarSocketsParams->setPublishSocketAddress('tcp://127.0.0.1:6263');
        $pulsarSocketsParams->setPullSocketAddress('tcp://127.0.0.1:6264');
        $pulsarSocketsParams->setReplyStackSocketAddress('tcp://127.0.0.1:6265');

        $publisherPulsarDto->setPulsarSocketsParams($pulsarSocketsParams);

        $pulsar->setPublisherPulsarDto($publisherPulsarDto);

        echo "start manage \n";

        $pulsar->manage();

        echo "finish manage \n";

        /* 1. Запускаем пульсар
         *
         * 2. Сравниваем количество резалт месседжес по формуле количество подписчиков равно сумме резальтDto и пуш msg
         * (count($this->resultingPushMessages) + $this->performerImitationRequests), $this->iAmSubscriber)
         *
         * 3. Можно смотреть отклонение в процентах
         *
         * 4. Можно смотреть с запуском дочерних процессов и их коннектом к ReplyStack
         *
         * */
    }


}