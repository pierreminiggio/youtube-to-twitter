<?php

namespace PierreMiniggio\YoutubeToTwitter\Repository;

use PierreMiniggio\DatabaseConnection\DatabaseConnection;

class NonUploadedVideoRepository
{
    public function __construct(private DatabaseConnection $connection)
    {}

    public function findByTwitterAndYoutubeChannelIds(int $twitterAccountId, int $youtubeChannelId): array
    {
        $this->connection->start();

        $postedTwitterPostIds = $this->connection->query('
            SELECT t.id
            FROM twitter_post as t
            RIGHT JOIN twitter_post_youtube_video as tpyv
            ON t.id = tpyv.twitter_id
            WHERE t.account_id = :account_id
        ', ['account_id' => $twitterAccountId]);
        $postedTwitterPostIds = array_map(fn ($entry) => (int) $entry['id'], $postedTwitterPostIds);

        $postsToPost = $this->connection->query('
            SELECT
                y.id,
                y.title,
                y.url
            FROM youtube_video as y
            ' . (
                $postedTwitterPostIds
                    ? 'LEFT JOIN twitter_post_youtube_video as tpyv
                    ON y.id = tpyv.youtube_id
                    AND tpyv.twitter_id IN (' . implode(', ', $postedTwitterPostIds) . ')'
                    : ''
            ) . '
            LEFT JOIN youtube_video_unpostable_on_twitter as yvuot
            ON yvuot.youtube_id = y.id
            
            WHERE y.channel_id = :channel_id
            AND yvuot.id IS NULL
            ' . ($postedTwitterPostIds ? 'AND tpyv.id IS NULL' : '') . '
            ;
        ', [
            'channel_id' => $youtubeChannelId
        ]);
        $this->connection->stop();

        return $postsToPost;
    }
}
