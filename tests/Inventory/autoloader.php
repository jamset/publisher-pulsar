<?php
/**
 * Created by PhpStorm.
 * User: nms
 * Date: 28.06.16
 * Time: 18:32
 */
include_once __DIR__ . '/../../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("React\\PublisherPulsar\\", __DIR__ . '/../src/');
$classLoader->register();