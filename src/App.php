<?php

namespace PierreMiniggio\YoutubeToTwitter;

use PierreMiniggio\YoutubeToTwitter\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToTwitter\Repository\LinkedChannelRepository;

class App
{
    public function run(): int
    {
        $code = 0;

        $config = require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php');

        if (empty($config['db'])) {
            echo 'No DB config';

            return $code;
        }

        $databaseConnection = (new DatabaseConnectionFactory())->makeFromConfig($config['db']);
        $channelRepository = new LinkedChannelRepository($databaseConnection);

        $linkedChannels = $channelRepository->findAll();

        if (! $linkedChannels) {
            echo 'No linked channels';

            return $code;
        }

        foreach ($linkedChannels as $linkedChannel) {
            
        }

        return $code;
    }
}
