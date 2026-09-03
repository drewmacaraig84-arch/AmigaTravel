<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Tests\TestCase;

class StaffReviewClaimLockTest extends TestCase
{
    public function test_review_claim_logic_and_permissions(): void
    {
        $staffA = new User();
        $staffA->id = 101;
        $staffA->name = 'Staff Alice';

        $staffB = new User();
        $staffB->id = 102;
        $staffB->name = 'Staff Bob';

        $booking = new Booking();
        $booking->id = 1;
        $booking->transaction_number = 'AGT-TEST-LOCK-001';

        // 1. Initial State: not claimed
        $this->assertFalse($booking->isReviewClaimed());
        $this->assertFalse($booking->isReviewClaimedBy($staffA));
        $this->assertFalse($booking->isReviewClaimedByOther($staffA));
        $this->assertEquals('Available', $booking->getReviewClaimStatusLabel($staffA));
        $this->assertNull($booking->getReviewClaimTooltip($staffA));

        // 2. Staff A Claims Booking
        $booking->review_claimed_by_user_id = $staffA->id;
        $booking->review_claimed_at = now();
        $booking->review_type = 'booking';
        $booking->setRelation('reviewClaimedBy', $staffA);

        $this->assertTrue($booking->isReviewClaimed());
        $this->assertTrue($booking->isReviewClaimedBy($staffA));
        $this->assertFalse($booking->isReviewClaimedByOther($staffA));
        $this->assertTrue($booking->isReviewClaimedByOther($staffB));
        $this->assertEquals('Claimed by you', $booking->getReviewClaimStatusLabel($staffA));
        $this->assertEquals('In Review by Staff Alice', $booking->getReviewClaimStatusLabel($staffB));
        $this->assertStringContainsString('Claimed by you', $booking->getReviewClaimTooltip($staffA));
        $this->assertStringContainsString('Being reviewed by Staff Alice', $booking->getReviewClaimTooltip($staffB));

        // 3. Collision check: Staff B cannot claim while Staff A has active lock
        $this->assertTrue($booking->isReviewClaimedByOther($staffB));

        // 4. Auto-Expiry check: Claim older than 10 minutes expires
        $booking->review_claimed_at = now()->subMinutes(11);
        $this->assertFalse($booking->isReviewClaimed());
        $this->assertFalse($booking->isReviewClaimedBy($staffA));
        $this->assertFalse($booking->isReviewClaimedByOther($staffB));
        $this->assertEquals('Available', $booking->getReviewClaimStatusLabel($staffB));

        // 5. Re-claim after expiration: Staff B claims
        $booking->review_claimed_by_user_id = $staffB->id;
        $booking->review_claimed_at = now();
        $booking->review_type = 'rebooking';
        $booking->setRelation('reviewClaimedBy', $staffB);

        $this->assertTrue($booking->isReviewClaimed());
        $this->assertTrue($booking->isReviewClaimedBy($staffB));
        $this->assertTrue($booking->isReviewClaimedByOther($staffA));
        $this->assertEquals('Claimed by you', $booking->getReviewClaimStatusLabel($staffB));
        $this->assertEquals('In Review by Staff Bob', $booking->getReviewClaimStatusLabel($staffA));

        // 6. Release: Claim is cleared
        $booking->review_claimed_by_user_id = null;
        $booking->review_claimed_at = null;
        $booking->review_type = null;
        $booking->setRelation('reviewClaimedBy', null);

        $this->assertFalse($booking->isReviewClaimed());
        $this->assertFalse($booking->isReviewClaimedByOther($staffA));
        $this->assertFalse($booking->isReviewClaimedByOther($staffB));
        $this->assertEquals('Available', $booking->getReviewClaimStatusLabel($staffA));
    }
}
