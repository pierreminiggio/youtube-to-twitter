<?php

namespace PierreMiniggio\YoutubeToTwitter;

use PierreMiniggio\TwitterHelpers\TwitterPoster;
use PierreMiniggio\YoutubeToTwitter\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToTwitter\Repository\LinkedChannelRepository;
use PierreMiniggio\YoutubeToTwitter\Repository\NonUploadedVideoRepository;

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
        $nonUploadedVideoRepository = new NonUploadedVideoRepository($databaseConnection);

        $linkedChannels = $channelRepository->findAll();

        if (! $linkedChannels) {
            echo 'No linked channels';

            return $code;
        }

        foreach ($linkedChannels as $linkedChannel) {
            $poster = new TwitterPoster(
                $linkedChannel['oauth_access_token'],
                $linkedChannel['oauth_access_token_secret'],
                $linkedChannel['consumer_key'],
                $linkedChannel['consumer_secret']
            );

            $postsToPost = $nonUploadedVideoRepository->findByTwitterAndYoutubeChannelIds($linkedChannel['t_id'], $linkedChannel['y_id']);
            var_dump($postsToPost);
        }

        return $code;
    }
}
