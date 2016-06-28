<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 21.06.16
 * Time: 18:47
 */
require_once "autoloader.php";

$replyStack = new \React\PublisherPulsar\ReplyStack();
$replyStack->startCommunication();