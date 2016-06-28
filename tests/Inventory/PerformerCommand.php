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
$performerSocketParams->setRequestPulsarRsSocketAddress('tcp://127.0.0.1:6265');
$performerSocketParams->setPublisherPulsarSocketAddress('tcp://127.0.0.1:6263');
$performerSocketParams->setPushPulsarSocketAddress('tcp://127.0.0.1:6264');

$performer->setSocketsParams($performerSocketParams);

$performer->requestForActionPermission();
$performer->waitAllowingSubscriptionMessage();

$performer->pushActionResultInfoWithoutPulsarCorrectionBehavior();

