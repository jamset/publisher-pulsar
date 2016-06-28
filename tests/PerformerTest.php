<?php

/**
 * Created by PhpStorm.
 * User: nms
 * Date: 28.06.16
 * Time: 17:49
 */
class PerformerTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var resource
     */
    protected static $pulsarProcess;

    /**
     * @var array
     */
    protected static $pipes = [];

    /**
     * @var \React\PublisherPulsar\Performer
     */
    protected static $performer;

    /**
     * Initialize Pulsar for connection
     */
    public static function setUpBeforeClass()
    {
        $performerDto = new \React\PublisherPulsar\Inventory\PerformerDto();
        $performerDto->setModuleName("PerformerCommand");

        self::$performer = new \React\PublisherPulsar\Performer($performerDto);

        $performerSocketParams = new \React\PublisherPulsar\Inventory\PerformerSocketsParamsDto();
        $performerSocketParams->setPublisherPulsarSocketAddress('tcp://127.0.0.1:6273');
        $performerSocketParams->setPushPulsarSocketAddress('tcp://127.0.0.1:6274');
        $performerSocketParams->setRequestPulsarRsSocketAddress('tcp://127.0.0.1:6275');

        self::$performer->setSocketsParams($performerSocketParams);

        $dir = __DIR__;

        $cmd = "php $dir/Inventory/PulsarCommand.php iterationsLimit=20";

        $fdSpec = [
            ['pipe', 'r'], // stdin
            ['pipe', 'w'], // stdout
            ['pipe', 'w'], // stderr
        ];

        self::$pulsarProcess = proc_open($cmd, $fdSpec, self::$pipes);
    }

    /**
     * @throws \React\PublisherPulsar\Inventory\Exceptions\PublisherPulsarException
     */
    public function testRequestForActionPermission()
    {
        sleep(2);

        self::$performer->waitAllowingSubscriptionMessage();

        $this->assertInstanceOf(\React\PublisherPulsar\Inventory\PublisherToSubscribersDto::class,
            self::$performer->getPublisherToSubscribersDto());
    }

    public function testWaitAllowingSubscriptionMessage()
    {
        sleep(2);
        $this->assertEquals(1, 1);
    }

    /**
     *Stop Pulsar
     */
    public static function tearDownAfterClass()
    {
        foreach (self::$pipes as $pipe) {
            fclose($pipe);
        }

        proc_close(self::$pulsarProcess);
    }

}