<?php

namespace App\Events;

use App\SpotVote;
use Illuminate\Queue\SerializesModels;

/**
 * Class OnSpotReviewDelete
 * @package App\Events
 *
 * Fires on spot review delete
 */
class OnSpotReviewDelete extends Event implements Feedable
{
    use SerializesModels;

    /**
     * @var SpotVote
     */
    public $review;

    /**
     * Create a new event instance.
     *
     * @param SpotVote $review
     */
    public function __construct(SpotVote $review)
    {
        $this->review = $review;
    }

    /**
     * {@inheritDoc}
     *
     * @return SpotVote
     */
    public function getFeedable()
    {
        return $this->review;
    }

    /**
     * {@inheritDoc}
     *
     * @return \App\User
     */
    public function getFeedSender()
    {
        return $this->review->user;
    }
}
