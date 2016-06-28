<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 03.06.16
 * Time: 15:13
 */

class PulsarTest extends PHPUnit_Framework_TestCase
{

    public function testPulsarLaunching()
    {
        $pulsar = new \React\PublisherPulsar\Pulsar();

        $pulsar->setIterationsLimit(4);

        $publisherPulsarDto = new \React\PublisherPulsar\Inventory\PublisherPulsarDto();

        $publisherPulsarDto->setPulsationIterationPeriod(1);
        $publisherPulsarDto->setSubscribersPerIteration(10);
        $publisherPulsarDto->setModuleName('react:pulsar');

        $dir = __DIR__;

        $publisherPulsarDto->setReplyStackCommandName("php $dir/Inventory/ReplyStackCommand.php");

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

        $pulsar->manage();

        $this->assertEquals(true, $pulsar->isIterationsLimitExceeded());
    }


}