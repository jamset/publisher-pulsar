<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 28.06.16
 * Time: 18:32
 */
require_once "autoloader.php";

$pulsar = new \React\PublisherPulsar\Pulsar();

$cliParams = [];
parse_str(implode('&', array_slice($argv, 1)), $cliParams);

if (isset($cliParams['iterationsLimit'])) {
    $pulsar->setIterationsLimit($cliParams['iterationsLimit']);
} else {
    $pulsar->setIterationsLimit(100);
}

$publisherPulsarDto = new \React\PublisherPulsar\Inventory\PublisherPulsarDto();

$publisherPulsarDto->setPulsationIterationPeriod(1);
$publisherPulsarDto->setSubscribersPerIteration(10);
$publisherPulsarDto->setModuleName('react:pulsar');

$dir = __DIR__;

$publisherPulsarDto->setReplyStackCommandName("php $dir/ReplyStackCommand.php");

$publisherPulsarDto->setPerformerContainerActionMaxExecutionTime(7);

$publisherPulsarDto->setMaxWaitReplyStackResult(7);

$pulsarSocketsParams = new \React\PublisherPulsar\Inventory\PulsarSocketsParamsDto();

$pulsarSocketsParams->setReplyToReplyStackSocketAddress('tcp://127.0.0.1:6271');
$pulsarSocketsParams->setPushToReplyStackSocketAddress('tcp://127.0.0.1:6272');
$pulsarSocketsParams->setPublishSocketAddress('tcp://127.0.0.1:6273');
$pulsarSocketsParams->setPullSocketAddress('tcp://127.0.0.1:6274');
$pulsarSocketsParams->setReplyStackSocketAddress('tcp://127.0.0.1:6275');

$publisherPulsarDto->setPulsarSocketsParams($pulsarSocketsParams);

$pulsar->setPublisherPulsarDto($publisherPulsarDto);

$pulsar->manage();