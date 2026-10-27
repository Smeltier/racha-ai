<?php

namespace Tests\Unit\Models\Invite;

use LogicException;
use DateTimeImmutable;
use App\Models\Invite\Invite;
use App\Models\Invite\InviteStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InviteTest extends TestCase
{
    public function testCreateStartAsPending() : void
    {
        $invite = Invite::create(1, 1, new DateTimeImmutable('+1 year'));
        $this->assertTrue($invite->isPending());
    }

    public function testCreateSetsTheCreationTime() : void
    {
        $before = new DateTimeImmutable();
        $invite = Invite::create(1, 1, new DateTimeImmutable('+1 year'));
        $after = new DateTimeImmutable();


        $this->assertGreaterThanOrEqual($before, $invite->getCreatedAt());
        $this->assertLessThanOrEqual($after, $invite->getCreatedAt());
    }

    public function testRestoreKeepsTheGivenData() : void
    {
        $createdAt = new DateTimeImmutable();
        $expiresAt = new DateTimeImmutable('+1 week');

        $invite = Invite::restore(15, 2, 10, InviteStatus::Pending, $createdAt, $expiresAt);

        $this->assertSame(15, $invite->getId());
        $this->assertSame(2, $invite->getInvitedUserId());
        $this->assertSame(10, $invite->getGroupId());
        $this->assertSame(InviteStatus::Pending, $invite->getStatus());
        $this->assertSame($expiresAt, $invite->getExpiresAt());
        $this->assertSame($createdAt, $invite->getCreatedAt());
    }

    #[DataProvider('statuses')]
    public function testRestoreAcceptsEveryStatus(InviteStatus $status): void
    {
        $invite = Invite::restore(
            15,
            2,
            10,
            $status,
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 year')
        );

        $this->assertSame($status, $invite->getStatus());
    }

    public static function statuses(): array
    {
        return [
            'pending' => [InviteStatus::Pending],
            'accepted' => [InviteStatus::Accepted],
            'declined' => [InviteStatus::Declined],
            'expired' => [InviteStatus::Expired],
        ];
    }

    public function testAcceptChangesPendingInviteToAccepted(): void
    {
        $invite = Invite::create(2, 10, new DateTimeImmutable('+1 week'));

        $invite->accept();

        $this->assertSame(InviteStatus::Accepted, $invite->getStatus());
        $this->assertFalse($invite->isPending());
        $this->assertFalse($invite->isExpired());
    }

    public function testDeclineChangesPendingInviteToDeclined(): void
    {
        $invite = Invite::create(2, 10, new DateTimeImmutable('+1 week'));

        $invite->decline();

        $this->assertSame(InviteStatus::Declined, $invite->getStatus());
        $this->assertFalse($invite->isPending());
        $this->assertFalse($invite->isExpired());
    }

    public function testExpireChangesPendingInviteToExpired(): void
    {
        $invite = Invite::create(2, 10, new DateTimeImmutable('+1 week'));

        $invite->expire();

        $this->assertSame(InviteStatus::Expired, $invite->getStatus());
        $this->assertFalse($invite->isPending());
        $this->assertTrue($invite->isExpired());
    }

    public function testIsExpiredAtReturnsTrueWhenInviteIsExpired(): void
    {
        $invite = Invite::create(2, 10, new DateTimeImmutable('+1 week'));
        $this->assertTrue($invite->isExpiredAt(new DateTimeImmutable('+2 weeks')));
    }

    public function testIsExpiredAtReturnsTrueForPendingInvitePastExpiration(): void
    {
        $expiresAt = new DateTimeImmutable('2026-10-27 10:00:00');
        $now = new DateTimeImmutable('2026-10-27 10:00:01');

        $invite = Invite::restore(
            15,
            2,
            10,
            InviteStatus::Pending,
            new DateTimeImmutable('2026-10-01 10:00:00'),
            $expiresAt
        );

        $this->assertTrue($invite->isExpiredAt($now));
    }

    public function testAcceptedInviteIsNotExpiredAtEvenAfterExpirationDate(): void
    {
        $invite = Invite::restore(
            15,
            2,
            10,
            InviteStatus::Accepted,
            new DateTimeImmutable('2026-10-01 10:00:00'),
            new DateTimeImmutable('2026-10-08 10:00:00')
        );

        $this->assertFalse(
            $invite->isExpiredAt(new DateTimeImmutable('2026-10-27 10:00:00'))
        );
    }

    #[DataProvider('nonPendingStatuses')]
    public function testAcceptThrowsWhenInviteIsNotPending(InviteStatus $status): void
    {
        $invite = Invite::restore(
            15,
            2,
            10,
            $status,
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 week')
        );

        $this->expectException(LogicException::class);

        $invite->accept();
    }

    #[DataProvider('nonPendingStatuses')]
    public function testDeclineThrowsWhenInviteIsNotPending(InviteStatus $status): void
    {
        $invite = Invite::restore(
            15,
            2,
            10,
            $status,
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 week')
        );

        $this->expectException(LogicException::class);

        $invite->decline();
    }

    #[DataProvider('nonPendingStatuses')]
    public function testExpireThrowsWhenInviteIsNotPending(InviteStatus $status): void
    {
        $invite = Invite::restore(
            15,
            2,
            10,
            $status,
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 week')
        );

        $this->expectException(LogicException::class);

        $invite->expire();
    }

    public static function nonPendingStatuses(): array
    {
        return [
            'accepted' => [InviteStatus::Accepted],
            'declined' => [InviteStatus::Declined],
            'expired' => [InviteStatus::Expired],
        ];
    }

    public function testAcceptKeepsStatusWhenInviteIsAlreadyDeclined(): void
    {
        $invite = Invite::restore(
            15,
            2,
            10,
            InviteStatus::Declined,
            new DateTimeImmutable(),
            new DateTimeImmutable('+1 week')
        );

        try {
            $invite->accept();
        } catch (LogicException) {
        }

        $this->assertSame(InviteStatus::Declined, $invite->getStatus());
    }

    public function testGettersReturnInviteData(): void
    {
        $createdAt = new DateTimeImmutable();
        $expiresAt = new DateTimeImmutable('+1 week');

        $invite = Invite::restore(
            15,
            2,
            10,
            InviteStatus::Pending,
            $createdAt,
            $expiresAt
        );

        $this->assertSame(15, $invite->getId());
        $this->assertSame(2, $invite->getInvitedUserId());
        $this->assertSame(10, $invite->getGroupId());
        $this->assertSame(InviteStatus::Pending, $invite->getStatus());
        $this->assertSame($createdAt, $invite->getCreatedAt());
        $this->assertSame($expiresAt, $invite->getExpiresAt());
    }
}