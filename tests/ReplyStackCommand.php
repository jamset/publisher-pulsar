<?php

/**
 * Created by PhpStorm.
 * User: nms
 * Date: 21.06.16
 * Time: 18:47
 */
include_once __DIR__ . '/../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("React\\PublisherPulsar\\", __DIR__ . '/../src/');
$classLoader->register();

$replyStack = new \React\PublisherPulsar\ReplyStack();
$replyStack->startCommunication();