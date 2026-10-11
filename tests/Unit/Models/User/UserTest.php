<?php

namespace Tests\Unit\Models\User;

use InvalidArgumentException;
use LogicException;
use DateTimeImmutable;
use App\Models\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    private const VALID_NAME = 'John Doe';
    private const VALID_EMAIL_VALUE = 'john@email.com';
    private const VALID_PASSWORD = 'password123';

    public function testRegisterHashesThePassword(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertNotSame(self::VALID_PASSWORD, $user->getPasswordHash());
        $this->assertNull($user->getId());
    }

    public function testVerifyPasswordAcceptsCorrectPassword(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertTrue($user->verifyPassword(self::VALID_PASSWORD));
    }

    public function testVerifyPasswordRejectsWrongPassword(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertFalse($user->verifyPassword('wrong_password'));
    }

    public function testShouldThrowWhenPasswordHasLessThan8Chars(): void
    {
        $this->expectException(InvalidArgumentException::class);

        User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, str_repeat('a', 7));
    }

    public function testShouldAcceptPasswordWithExactly8Chars(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, str_repeat('a', 8));

        $this->assertTrue($user->verifyPassword(str_repeat('a', 8)));
    }

    #[DataProvider('invalidNames')]
    public function testShouldThrowWhenNameIsInvalid(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);

        User::register($name, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
    }

    public static function invalidNames(): array
    {
        return [
            'empty' => [''],
            'blank' => ['   '],
            'too long' => [str_repeat('a', 151)],
        ];
    }

    public function testShouldAcceptNameWithExactly150Chars(): void
    {
        $user = User::register(str_repeat('a', 150), self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertSame(150, mb_strlen($user->getName()));
    }

    public function testShouldTrimName(): void
    {
        $user = User::register('  John  ', self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertSame('John', $user->getName());
    }

    public function testAssignIdSetsTheId(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $user->assignId(10);

        $this->assertSame(10, $user->getId());
    }

    public function testShouldThrowWhenIdIsAlreadyAssigned(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $user->assignId(1);

        $this->expectException(LogicException::class);

        $user->assignId(2);
    }

    public function testReconstituteKeepsTheGivenHash(): void
    {
        $hash = password_hash(self::VALID_PASSWORD, PASSWORD_ARGON2ID);

        $user = User::reconstitute(5, self::VALID_NAME, self::VALID_EMAIL_VALUE, $hash, null);

        $this->assertSame($hash, $user->getPasswordHash());
        $this->assertSame(5, $user->getId());
        $this->assertTrue($user->verifyPassword(self::VALID_PASSWORD));
    }

    public function testChangeNameKeepsOldValueWhenInvalid(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        try {
            $user->changeName('   ');
        } catch (InvalidArgumentException) {
        }

        $this->assertSame(self::VALID_NAME, $user->getName());
    }

    public function testChangeEmailKeepsOldValueWhenInvalid(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        try {
            $user->changeEmail('invalid');
        } catch (InvalidArgumentException) {
        }

        $this->assertSame(self::VALID_EMAIL_VALUE, $user->getEmail()->getValue());
    }

    public function testShouldThrowWhenPasswordHasLessThan8MultibyteChars(): void
    {
        $this->expectException(InvalidArgumentException::class);

        User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, 'áááá');
    }

    public function testShouldAcceptName150MultibyteChars(): void
    {
        $user = User::register(str_repeat('á', 150), self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertSame(150, mb_strlen($user->getName()));
    }

    public function testSamePasswordGeneratesDifferentHashes(): void
    {
        $first = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $second = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->assertNotSame($first->getPasswordHash(), $second->getPasswordHash());
    }

    public function testReconstituteThrowsWhenDataIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        User::reconstitute(1, '', self::VALID_EMAIL_VALUE, 'hash', null);
    }

    public function testDeleteAnonymizesUser(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $user->assignId(7);
        $now = new DateTimeImmutable('2026-10-11 18:00:00');

        $user->delete($now);

        $this->assertSame('Deleted user', $user->getName());
        $this->assertSame('deleted_7@invalid.example', $user->getEmail()->getValue());
        $this->assertSame('', $user->getPasswordHash());
        $this->assertSame($now, $user->getDeletedAt());
        $this->assertTrue($user->isDeleted());
    }

    public function testReconstituteAcceptsDeletedUser(): void
    {
        $deletedAt = new DateTimeImmutable('2026-10-11 18:00:00');

        $user = User::reconstitute(3, 'Deleted user', 'deleted_3@invalid.example', '', $deletedAt);

        $this->assertTrue($user->isDeleted());
        $this->assertSame($deletedAt, $user->getDeletedAt());
    }
}
