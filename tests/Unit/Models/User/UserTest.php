<?php

namespace Tests\Unit\Models\User;

use LogicException;
use DateTimeImmutable;
use App\Models\User\User;
use App\Exceptions\ValidationException;
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
        $this->expectException(ValidationException::class);

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
        $this->expectException(ValidationException::class);

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
        } catch (ValidationException) {
        }

        $this->assertSame(self::VALID_NAME, $user->getName());
    }

    public function testChangeEmailKeepsOldValueWhenInvalid(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        try {
            $user->changeEmail('invalid');
        } catch (ValidationException) {
        }

        $this->assertSame(self::VALID_EMAIL_VALUE, $user->getEmail()->getValue());
    }

    public function testShouldThrowWhenPasswordHasLessThan8MultibyteChars(): void
    {
        $this->expectException(ValidationException::class);

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
        $this->expectException(ValidationException::class);

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

    public function testChangePasswordReplacesTheHash(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $oldHash = $user->getPasswordHash();

        $user->changePassword(self::VALID_PASSWORD, 'new_password123');

        $this->assertNotSame($oldHash, $user->getPasswordHash());
    }

    public function testChangePasswordAcceptsOnlyTheNewPasswordAfterwards(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $user->changePassword(self::VALID_PASSWORD, 'new_password123');

        $this->assertTrue($user->verifyPassword('new_password123'));
        $this->assertFalse($user->verifyPassword(self::VALID_PASSWORD));
    }

    public function testChangePasswordThrowsWhenCurrentPasswordIsWrong(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->expectException(ValidationException::class);

        $user->changePassword('wrong_password', 'new_password123');
    }

    public function testChangePasswordKeepsOldHashWhenCurrentPasswordIsWrong(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $oldHash = $user->getPasswordHash();

        try {
            $user->changePassword('wrong_password', 'new_password123');
        } catch (ValidationException) {
        }

        $this->assertSame($oldHash, $user->getPasswordHash());
        $this->assertTrue($user->verifyPassword(self::VALID_PASSWORD));
    }

    public function testChangePasswordThrowsWhenNewPasswordHasLessThan8Chars(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->expectException(ValidationException::class);

        $user->changePassword(self::VALID_PASSWORD, str_repeat('a', 7));
    }

    public function testChangePasswordKeepsOldHashWhenNewPasswordIsTooShort(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $oldHash = $user->getPasswordHash();

        try {
            $user->changePassword(self::VALID_PASSWORD, str_repeat('a', 7));
        } catch (ValidationException) {
        }

        $this->assertSame($oldHash, $user->getPasswordHash());
        $this->assertTrue($user->verifyPassword(self::VALID_PASSWORD));
    }

    public function testChangePasswordAcceptsNewPasswordWithExactly8Chars(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $user->changePassword(self::VALID_PASSWORD, str_repeat('a', 8));

        $this->assertTrue($user->verifyPassword(str_repeat('a', 8)));
    }

    public function testChangePasswordThrowsWhenNewPasswordHasLessThan8MultibyteChars(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->expectException(ValidationException::class);

        $user->changePassword(self::VALID_PASSWORD, 'áááá');
    }

    public function testChangePasswordChecksCurrentPasswordBeforeValidatingTheNewOne(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);

        $this->expectException(ValidationException::class);

        $user->changePassword('wrong_password', 'short');
    }

    public function testChangePasswordGeneratesDifferentHashForTheSamePassword(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $oldHash = $user->getPasswordHash();

        $user->changePassword(self::VALID_PASSWORD, self::VALID_PASSWORD);

        $this->assertNotSame($oldHash, $user->getPasswordHash());
        $this->assertTrue($user->verifyPassword(self::VALID_PASSWORD));
    }

    public function testChangePasswordThrowsWhenUserIsDeleted(): void
    {
        $user = User::register(self::VALID_NAME, self::VALID_EMAIL_VALUE, self::VALID_PASSWORD);
        $user->assignId(7);
        $user->delete(new DateTimeImmutable('2026-10-11 18:00:00'));

        $this->expectException(LogicException::class);

        $user->changePassword(self::VALID_PASSWORD, 'new_password123');
    }

    public function testChangePasswordWorksOnReconstitutedUser(): void
    {
        $hash = password_hash(self::VALID_PASSWORD, PASSWORD_ARGON2ID);
        $user = User::reconstitute(5, self::VALID_NAME, self::VALID_EMAIL_VALUE, $hash, null);

        $user->changePassword(self::VALID_PASSWORD, 'new_password123');

        $this->assertTrue($user->verifyPassword('new_password123'));
    }
}
