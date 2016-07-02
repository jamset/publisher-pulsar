<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 03.06.16
 * Time: 15:13
 */

class PulsarTest extends PHPUnit_Framework_TestCase
{

    /**
     * @throws \React\PublisherPulsar\Inventory\Exceptions\PublisherPulsarException
     */
    public function testPulsarLaunching()
    {
        $pulsar = new \React\PublisherPulsar\Pulsar();

        $pulsar->setIterationsLimit(10);

        $publisherPulsarDto = new \React\PublisherPulsar\Inventory\PublisherPulsarDto();

        $publisherPulsarDto->setModuleName('react:pulsar');
        $publisherPulsarDto->setLogger(new \Monolog\Logger("Pulsar-test-logger"));

        $dir = __DIR__;
        $publisherPulsarDto->setReplyStackCommandName("php $dir/Inventory/ReplyStackCommand.php");

        $publisherPulsarDto->initDefaultPulsarSocketsParams();

        $pulsar->setPublisherPulsarDto($publisherPulsarDto);

        try {

            $pulsar->manage();

        } catch (\Exception $e) {
        }

        $this->assertEquals(true, $pulsar->isIterationsLimitExceeded());
    }


}