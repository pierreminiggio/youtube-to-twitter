<?php

namespace PierreMiniggio\YoutubeToTwitter;

use PierreMiniggio\YoutubeToTwitter\Connection\DatabaseConnectionFactory;

class App
{
    public function run(): int
    {
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');

        if (! empty($config['db'])) {
            
        }

        return 0;
    }
}
