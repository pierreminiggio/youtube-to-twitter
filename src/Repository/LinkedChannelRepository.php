<?php

namespace PierreMiniggio\YoutubeToTwitter\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class LinkedChannelRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findAll(): array
    {
        $this->connection->start();
        $channels = $this->connection->query('
            SELECT
                tayc.youtube_id as y_id,
                t.id as t_id,
                t.oauth_access_token,
                t.oauth_access_token_secret,
                t.consumer_key,
                t.consumer_secret,
                t.tweet_prefix
            FROM twitter_account as t
            RIGHT JOIN twitter_account_youtube_channel as tayc
                ON t.id = tayc.twitter_id
        ', []);
        $this->connection->stop();

        return $channels;
    }
}
