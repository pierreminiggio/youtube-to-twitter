<?php

namespace PierreMiniggio\YoutubeToTwitter;

use PierreMiniggio\TwitterHelpers\TwitterPoster;
use PierreMiniggio\YoutubeToTwitter\Connection\DatabaseConnectionFactory;
use PierreMiniggio\YoutubeToTwitter\Repository\LinkedChannelRepository;
use PierreMiniggio\YoutubeToTwitter\Repository\NonUploadedVideoRepository;
use PierreMiniggio\YoutubeToTwitter\Repository\VideoToUploadRepository;

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
        $videoToUploadRepository = new VideoToUploadRepository($databaseConnection);

        $linkedChannels = $channelRepository->findAll();

        if (! $linkedChannels) {
            echo 'No linked channels';

            return $code;
        }

        foreach ($linkedChannels as $linkedChannel) {
            echo PHP_EOL . PHP_EOL . 'Checking account ' . $linkedChannel['t_id'] . '...';

            $poster = new TwitterPoster(
                $linkedChannel['oauth_access_token'],
                $linkedChannel['oauth_access_token_secret'],
                $linkedChannel['consumer_key'],
                $linkedChannel['consumer_secret']
            );

            $postsToPost = $nonUploadedVideoRepository->findByTwitterAndYoutubeChannelIds($linkedChannel['t_id'], $linkedChannel['y_id']);
            echo PHP_EOL . count($postsToPost) . ' post(s) to post :' . PHP_EOL;
            
            foreach ($postsToPost as $postToPost) {
                echo PHP_EOL . 'Posting ' . $postToPost['title'] . ' ...';
                $res = json_decode($poster->updateStatus(
                    str_replace(
                        '[youtube_link]',
                        $postToPost['url'],
                        str_replace(
                            '[youtube_title]',
                            $postToPost['title'],
                            $linkedChannel['tweet_content']
                        )
                    )
                ), true);
                if (isset($res['id'])) {
                    $videoToUploadRepository->insertVideoIfNeeded($res['id'], $linkedChannel['t_id'], $postToPost['id']);
                    echo PHP_EOL . $postToPost['title'] . ' posted !';
                } else {
                    echo PHP_EOL . 'Error while posting ' . $postToPost['title'];
                }
            }

            echo PHP_EOL . PHP_EOL . 'Done for account ' . $linkedChannel['t_id'] . ' !';
        }

        return $code;
    }
}
