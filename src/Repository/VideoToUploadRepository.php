<?php

namespace PierreMiniggio\YoutubeToTwitter\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class VideoToUploadRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function insertVideoIfNeeded(
        string $twitterId,
        int $twitterAccountId,
        int $youtubeVideoId
    ): void
    {
        $this->connection->start();
        $postQueryParams = [
            'account_id' => $twitterAccountId,
            'twitter_id' => $twitterId
        ];
        $findPostIdQuery = ['
            SELECT id FROM twitter_post
            WHERE account_id = :account_id
            AND twitter_id = :twitter_id
            ;
        ', $postQueryParams];
        $queriedIds = $this->connection->query(...$findPostIdQuery);
        
        if (! $queriedIds) {
            $this->connection->exec('
                INSERT INTO twitter_post (account_id, twitter_id)
                VALUES (:account_id, :twitter_id)
                ;
            ', $postQueryParams);
            $queriedIds = $this->connection->query(...$findPostIdQuery);
        }

        $postId = (int) $queriedIds[0]['id'];
        
        $pivotQueryParams = [
            'twitter_id' => $postId,
            'youtube_id' => $youtubeVideoId
        ];

        $queriedPivotIds = $this->connection->query('
            SELECT id FROM twitter_post_youtube_video
            WHERE twitter_id = :twitter_id
            AND youtube_id = :youtube_id
            ;
        ', $pivotQueryParams);
        
        if (! $queriedPivotIds) {
            $this->connection->exec('
                INSERT INTO twitter_post_youtube_video (twitter_id, youtube_id)
                VALUES (:twitter_id, :youtube_id)
                ;
            ', $pivotQueryParams);
        }

        $this->connection->stop();
    }
}
