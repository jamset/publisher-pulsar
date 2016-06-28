<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 23.06.16
 * Time: 11:49
 */
require_once "autoloader.php";

$performerDto = new \React\PublisherPulsar\Inventory\PerformerDto();
$performerDto->setModuleName("PerformerCommand");

$performer = new \React\PublisherPulsar\Performer($performerDto);

$performerSocketParams = new \React\PublisherPulsar\Inventory\PerformerSocketsParamsDto();
$performerSocketParams->setPublisherPulsarSocketAddress('tcp://127.0.0.1:6273');
$performerSocketParams->setPushPulsarSocketAddress('tcp://127.0.0.1:6274');
$performerSocketParams->setRequestPulsarRsSocketAddress('tcp://127.0.0.1:6275');

$performer->setSocketsParams($performerSocketParams);

$performer->requestForActionPermission();
$performer->waitAllowingSubscriptionMessage();

$performer->pushActionResultInfoWithoutPulsarCorrectionBehavior();

