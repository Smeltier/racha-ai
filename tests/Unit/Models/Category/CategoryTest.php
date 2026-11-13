<?php

namespace Tests\Unit\Models\Category;

use App\Exceptions\ValidationException;
use App\Models\Category\Category;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    private string $validName = 'Valid Category Name';
    private string $validColor = 'FF5733';

    public function testShouldCreateValidCategory() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);

        $this->assertNull($category->getId());
        $this->assertSame(1, $category->getGroupId());
        $this->assertSame($this->validName, $category->getName());
        $this->assertSame($this->validColor, $category->getColor());
    }

    public function testShouldReconstituteValidCategory() : void
    {
        $category = Category::reconstitute(1, 1, $this->validName, $this->validColor);

        $this->assertSame(1, $category->getId());
        $this->assertSame(1, $category->getGroupId());
        $this->assertSame($this->validName, $category->getName());
        $this->assertSame($this->validColor, $category->getColor());
    }

    public function testChangeNameShouldTrimTheName() : void
    {
        $category = Category::create(1, 'Name', $this->validColor);
        $category->changeName('  ' . $this->validName . '  ');
        $this->assertSame($this->validName, $category->getName());
    }

    public function testShouldThrowExceptionWhenNameIsEmpty() : void
    {
        $this->expectException(ValidationException::class);
        Category::create(1, '   ', $this->validColor);
    }

    public function testShouldThrowExceptionWhenNameIsBlank() : void
    {
        $this->expectException(ValidationException::class);
        Category::create(1, '', $this->validColor);
    }

    public function testShouldThrowExceptionWhenNameIsTooLong() : void
    {
        $this->expectException(ValidationException::class);
        Category::create(1, str_repeat('a', 51), $this->validColor);
    }

    public function testShouldMaintainNameWhenChangingToInvalidName() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);

        try {
            $category->changeName('');
            $this->fail('Esperava-se ValidationException, mas nenhuma exceção foi lançada.');
        } catch (ValidationException) {
            // Expected exception.
        }

        $this->assertSame($this->validName, $category->getName());
    }

    public function testChangeColorShouldTrimTheColor() : void
    {
        $category = Category::create(1, $this->validName, 'FFFFFF');
        $category->changeColor('  ' . $this->validColor . '  ');
        $this->assertSame($this->validColor, $category->getColor());
    }

    public function testShouldThrowExceptionWhenColorIsEmpty() : void
    {
        $this->expectException(ValidationException::class);
        Category::create(1, $this->validName, '   ');
    }

    public function testShouldThrowExceptionWhenColorIsBlank() : void
    {
        $this->expectException(ValidationException::class);
        Category::create(1, $this->validName, '');
    }

    public function testShouldThrowExceptionWhenColorIsInvalid() : void
    {
        $this->expectException(ValidationException::class);
        Category::create(1, $this->validName, 'ZZZZZZ');
    }

    public function testShouldMaintainColorWhenChangingToInvalidColor() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);

        try {
            $category->changeColor('ZZZZZZ');
            $this->fail('Esperava-se ValidationException, mas nenhuma exceção foi lançada.');
        } catch (ValidationException) {
            // Expected exception.
        }

        $this->assertSame($this->validColor, $category->getColor());
    }

    public function testToStringReturnsHexColorCode() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);
        $this->assertSame('#' . $this->validColor, (string)$category);
    }

    public function testToStringReturnsHexColorCodeAfterChangingColor() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);
        $newColor = '00FF00';
        $category->changeColor($newColor);
        $this->assertSame('#' . $newColor, (string)$category);
    }

    public function testAssignIdShouldNotChangeId() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);
        $category->assignId(5);
        $this->assertSame(5, $category->getId());

        try {
            $category->assignId(10);
            $this->fail('Esperava-se ValidationException, mas nenhuma exceção foi lançada.');
        } catch (ValidationException) {
            // Expected exception.
        }

        $this->assertSame(5, $category->getId());
    }

    public function testShouldAcceptChangeToValidName() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);
        $newName = 'New Valid Name';
        $category->changeName($newName);
        $this->assertSame($newName, $category->getName());
    }

    public function testShouldAcceptChangeToValidColor() : void
    {
        $category = Category::create(1, $this->validName, $this->validColor);
        $newColor = '123ABC';
        $category->changeColor($newColor);
        $this->assertSame($newColor, $category->getColor());
    }

    public function testShouldAcceptNameWith50Characters() : void
    {
        $name = str_repeat('a', 50);
        $category = Category::create(1, $name, $this->validColor);
        $this->assertSame($name, $category->getName());
    }

    public function testShouldAcceptUnicodeName() : void
    {
        $unicodeName = 'カテゴリー'; // "Category" in Japanese
        $category = Category::create(1, $unicodeName, $this->validColor);
        $this->assertSame($unicodeName, $category->getName());
    }

    #[DataProvider('validColorProvider')]
    public function testShouldAcceptValidHexColorCodes($color) : void
    {
        $category = Category::create(1, $this->validName, $color);
        $this->assertSame($color, $category->getColor());
    }

    public static function validColorProvider(): array
    {
        return [
            ['FFFFFF'],
            ['000000'],
            ['FF5733'],
            ['123ABC'],
            ['abcdef'],
            ['A1B2C3'],
        ];
    }
}