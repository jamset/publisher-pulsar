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
$publisherPulsarDto->setModuleName('react:pulsar');

$dir = __DIR__;

$publisherPulsarDto->setReplyStackCommandName("php $dir/ReplyStackCommand.php");
$publisherPulsarDto->initDefaultPulsarSocketsParams();


$pulsar->setPublisherPulsarDto($publisherPulsarDto);

$pulsar->manage();