<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\Rating;
use Iranimij\OpenLabel\Model\Condition\ReviewCount;

class ReviewTest extends ConditionTestCase
{
    public function testRatingIsComparedInStars(): void
    {
        $condition = $this->condition(Rating::class, 'rating', '>=', '4');

        self::assertTrue($condition->validate($this->product(['rating_summary' => 85])), '85 % = 4.25 stars');
        self::assertFalse($condition->validate($this->product(['rating_summary' => 70])), '70 % = 3.5 stars');
        self::assertFalse($condition->validate($this->product(['rating_summary' => null])), 'no reviews, no rating');
    }

    public function testReviewCountComparesApprovedReviews(): void
    {
        $condition = $this->condition(ReviewCount::class, 'review_count', '>=', '3');

        self::assertTrue($condition->validate($this->product(['reviews_count' => 3])));
        self::assertFalse($condition->validate($this->product(['reviews_count' => 2])));
        self::assertFalse($condition->validate($this->product(['reviews_count' => null])));
    }

    public function testBothJoinTheReviewSummaryOfTheRulesStore(): void
    {
        $rating = $this->condition(Rating::class, 'rating', '>=', '4');
        $count = $this->condition(ReviewCount::class, 'review_count', '>=', '3');

        self::assertSame($rating->getTablesToJoin(), $count->getTablesToJoin(), 'same alias and condition so the builder joins once');
        self::assertSame('review_entity_summary', $rating->getTablesToJoin()['ol_review']['name']);
        self::assertStringContainsString('ol_review.store_id = 1', $rating->getTablesToJoin()['ol_review']['condition']);
        self::assertStringContainsString('rating_summary / 20', (string) $rating->getMappedSqlField());
        self::assertSame('ol_review.reviews_count', (string) $count->getMappedSqlField());
        self::assertFalse($rating->requiresCustomerGroup());
    }
}
