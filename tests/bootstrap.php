<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 31.05.16
 * Time: 19:40
 */
include_once __DIR__ . '/../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("React\\PublisherPulsar\\", __DIR__ . '/../src/');
$classLoader->register();