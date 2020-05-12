<?php

require_once __DIR__ . '/vendor/autoload.php';

(new \src\App())->run([
    \src\controller\CreateImage::class,
    \src\controller\Worker::class,
    \src\controller\TestData::class
]);