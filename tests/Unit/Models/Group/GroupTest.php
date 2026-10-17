<?php

namespace Tests\Unit\Models\Group;

use LogicException;
use DateTimeImmutable;
use App\Models\Group\Group;
use App\Models\Group\GroupMember;
use App\Models\Group\GroupRole;
use App\Exceptions\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GroupTest extends TestCase
{
    private const VALID_NAME = 'Trip to Rio';
    private const CREATOR_ID = 1;
    private const MEMBER_ID = 2;
    private const OTHER_ID = 3;

    private function newGroup(): Group
    {
        return Group::create(self::VALID_NAME, self::CREATOR_ID);
    }

    private function groupWithMember(): Group
    {
        $group = $this->newGroup();
        $group->addMember(self::MEMBER_ID);

        return $group;
    }

    private function deletedGroup(): Group
    {
        $group = $this->groupWithMember();
        $group->delete();

        return $group;
    }

    private function findMember(Group $group, int $userId): GroupMember
    {
        foreach ($group->getMembers() as $member) {
            if ($member->userId === $userId) {
                return $member;
            }
        }

        $this->fail('Member not found.');
    }

    public function testCreateStartsWithCreatorAsAdmin(): void
    {
        $group = $this->newGroup();

        $this->assertNull($group->getId());
        $this->assertNull($group->getDeletedAt());
        $this->assertFalse($group->isDeleted());
        $this->assertSame(1, $group->memberCount());
        $this->assertTrue($group->hasMember(self::CREATOR_ID));
        $this->assertTrue($group->isAdmin(self::CREATOR_ID));
    }

    #[DataProvider('invalidNames')]
    public function testShouldThrowWhenNameIsInvalid(string $name): void
    {
        $this->expectException(ValidationException::class);

        Group::create($name, self::CREATOR_ID);
    }

    public static function invalidNames(): array
    {
        return [
            'empty' => [''],
            'blank' => ['   '],
            'too long' => [str_repeat('a', 201)],
            'too long multibyte' => [str_repeat('á', 201)],
        ];
    }

    public function testShouldAcceptNameWithExactly200Chars(): void
    {
        $group = Group::create(str_repeat('a', 200), self::CREATOR_ID);

        $this->assertSame(200, mb_strlen($group->getName()));
    }

    public function testShouldAcceptName200MultibyteChars(): void
    {
        $group = Group::create(str_repeat('á', 200), self::CREATOR_ID);

        $this->assertSame(200, mb_strlen($group->getName()));
    }

    public function testShouldTrimName(): void
    {
        $group = Group::create('  Trip  ', self::CREATOR_ID);

        $this->assertSame('Trip', $group->getName());
    }

    public function testAssignIdSetsTheId(): void
    {
        $group = $this->newGroup();

        $group->assignId(10);

        $this->assertSame(10, $group->getId());
    }

    public function testShouldThrowWhenIdIsAlreadyAssigned(): void
    {
        $group = $this->newGroup();
        $group->assignId(1);

        $this->expectException(LogicException::class);

        $group->assignId(2);
    }

    public function testReconstituteKeepsTheGivenData(): void
    {
        $createdAt = new DateTimeImmutable('2026-10-01 10:00:00');
        $members = [
            new GroupMember(self::CREATOR_ID, GroupRole::Admin, $createdAt),
            new GroupMember(self::MEMBER_ID, GroupRole::Member, $createdAt),
        ];

        $group = Group::reconstitute(5, self::VALID_NAME, $createdAt, null, $members);

        $this->assertSame(5, $group->getId());
        $this->assertSame(self::VALID_NAME, $group->getName());
        $this->assertSame($createdAt, $group->getCreatedAt());
        $this->assertSame(2, $group->memberCount());
        $this->assertTrue($group->isAdmin(self::CREATOR_ID));
        $this->assertFalse($group->isAdmin(self::MEMBER_ID));
    }

    public function testReconstituteAcceptsDeletedGroup(): void
    {
        $deletedAt = new DateTimeImmutable('2026-10-11 18:00:00');

        $group = Group::reconstitute(3, self::VALID_NAME, new DateTimeImmutable(), $deletedAt, []);

        $this->assertTrue($group->isDeleted());
        $this->assertSame($deletedAt, $group->getDeletedAt());
    }

    public function testRenameChangesTheName(): void
    {
        $group = $this->newGroup();

        $group->rename('  New name  ');

        $this->assertSame('New name', $group->getName());
    }

    public function testRenameKeepsOldValueWhenInvalid(): void
    {
        $group = $this->newGroup();

        try {
            $group->rename('   ');
        } catch (ValidationException) {
        }

        $this->assertSame(self::VALID_NAME, $group->getName());
    }

    public function testRenameThrowsWhenGroupIsDeleted(): void
    {
        $group = $this->deletedGroup();

        $this->expectException(LogicException::class);

        $group->rename('New name');
    }

    public function testAddMemberAddsAsRegularMember(): void
    {
        $group = $this->newGroup();

        $group->addMember(self::MEMBER_ID);

        $this->assertSame(2, $group->memberCount());
        $this->assertTrue($group->hasMember(self::MEMBER_ID));
        $this->assertFalse($group->isAdmin(self::MEMBER_ID));
        $this->assertSame(GroupRole::Member, $this->findMember($group, self::MEMBER_ID)->role);
    }

    public function testAddMemberThrowsWhenUserIsAlreadyMember(): void
    {
        $group = $this->groupWithMember();

        $this->expectException(ValidationException::class);

        $group->addMember(self::MEMBER_ID);
    }

    public function testAddMemberThrowsWhenCreatorIsAddedAgain(): void
    {
        $group = $this->newGroup();

        $this->expectException(ValidationException::class);

        $group->addMember(self::CREATOR_ID);
    }

    public function testAddMemberThrowsWhenGroupIsDeleted(): void
    {
        $group = $this->deletedGroup();

        $this->expectException(LogicException::class);

        $group->addMember(self::OTHER_ID);
    }

    public function testRemoveMemberRemovesRegularMember(): void
    {
        $group = $this->groupWithMember();

        $group->removeMember(self::MEMBER_ID);

        $this->assertFalse($group->hasMember(self::MEMBER_ID));
        $this->assertSame(1, $group->memberCount());
    }

    public function testRemoveMemberThrowsWhenUserIsNotMember(): void
    {
        $group = $this->newGroup();

        $this->expectException(ValidationException::class);

        $group->removeMember(self::OTHER_ID);
    }

    public function testRemoveMemberThrowsWhenRemovingTheLastAdmin(): void
    {
        $group = $this->groupWithMember();

        $this->expectException(ValidationException::class);

        $group->removeMember(self::CREATOR_ID);
    }

    public function testRemoveMemberKeepsTheLastAdminWhenRejected(): void
    {
        $group = $this->groupWithMember();

        try {
            $group->removeMember(self::CREATOR_ID);
        } catch (ValidationException) {
        }

        $this->assertTrue($group->isAdmin(self::CREATOR_ID));
        $this->assertSame(2, $group->memberCount());
    }

    public function testRemoveMemberAllowsRemovingAnAdminWhenAnotherAdminExists(): void
    {
        $group = $this->groupWithMember();
        $group->promote(self::MEMBER_ID);

        $group->removeMember(self::CREATOR_ID);

        $this->assertFalse($group->hasMember(self::CREATOR_ID));
        $this->assertTrue($group->isAdmin(self::MEMBER_ID));
    }

    public function testRemoveMemberThrowsWhenGroupIsDeleted(): void
    {
        $group = $this->deletedGroup();

        $this->expectException(LogicException::class);

        $group->removeMember(self::MEMBER_ID);
    }

    public function testPromoteMakesMemberAnAdmin(): void
    {
        $group = $this->groupWithMember();

        $group->promote(self::MEMBER_ID);

        $this->assertTrue($group->isAdmin(self::MEMBER_ID));
    }

    public function testPromoteKeepsTheJoinedAt(): void
    {
        $group = $this->groupWithMember();
        $joinedAt = $this->findMember($group, self::MEMBER_ID)->joinedAt;

        $group->promote(self::MEMBER_ID);

        $this->assertSame($joinedAt, $this->findMember($group, self::MEMBER_ID)->joinedAt);
    }

    public function testPromoteThrowsWhenUserIsNotMember(): void
    {
        $group = $this->newGroup();

        $this->expectException(ValidationException::class);

        $group->promote(self::OTHER_ID);
    }

    public function testPromoteThrowsWhenGroupIsDeleted(): void
    {
        $group = $this->deletedGroup();

        $this->expectException(LogicException::class);

        $group->promote(self::MEMBER_ID);
    }

    public function testDemoteMakesAdminARegularMember(): void
    {
        $group = $this->groupWithMember();
        $group->promote(self::MEMBER_ID);

        $group->demote(self::CREATOR_ID);

        $this->assertFalse($group->isAdmin(self::CREATOR_ID));
        $this->assertTrue($group->hasMember(self::CREATOR_ID));
    }

    public function testDemoteThrowsWhenDemotingTheLastAdmin(): void
    {
        $group = $this->groupWithMember();

        $this->expectException(ValidationException::class);

        $group->demote(self::CREATOR_ID);
    }

    public function testDemoteKeepsTheLastAdminWhenRejected(): void
    {
        $group = $this->groupWithMember();

        try {
            $group->demote(self::CREATOR_ID);
        } catch (ValidationException) {
        }

        $this->assertTrue($group->isAdmin(self::CREATOR_ID));
    }

    public function testDemoteThrowsWhenUserIsNotMember(): void
    {
        $group = $this->newGroup();

        $this->expectException(ValidationException::class);

        $group->demote(self::OTHER_ID);
    }

    public function testDemoteThrowsWhenGroupIsDeleted(): void
    {
        $group = $this->deletedGroup();

        $this->expectException(LogicException::class);

        $group->demote(self::CREATOR_ID);
    }

    public function testDeleteMarksTheGroupAsDeleted(): void
    {
        $group = $this->newGroup();

        $group->delete();

        $this->assertTrue($group->isDeleted());
        $this->assertInstanceOf(DateTimeImmutable::class, $group->getDeletedAt());
    }

    public function testDeleteThrowsWhenGroupIsAlreadyDeleted(): void
    {
        $group = $this->deletedGroup();

        $this->expectException(LogicException::class);

        $group->delete();
    }

    public function testHasMemberAndIsAdminReturnFalseForStrangers(): void
    {
        $group = $this->newGroup();

        $this->assertFalse($group->hasMember(self::OTHER_ID));
        $this->assertFalse($group->isAdmin(self::OTHER_ID));
    }

    public function testGetMembersReturnsAReindexedList(): void
    {
        $group = $this->groupWithMember();
        $group->removeMember(self::MEMBER_ID);
        $group->addMember(self::OTHER_ID);

        $this->assertSame([0, 1], array_keys($group->getMembers()));
    }
}
