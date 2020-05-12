<?php

namespace src;

use Symfony\Component\Console\Application;

class App
{
    public function run(array $config): void
    {
        $app = new Application();

        foreach ($config as $command) {
            $app->add(new $command);
        }

        $app->run();
    }
}