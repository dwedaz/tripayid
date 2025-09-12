<?php

namespace Tripay\PPOB\Tests\Unit\DTO;

use Tripay\PPOB\DTO\DataTransferObject;
use Tripay\PPOB\Tests\TestCase;

// Test DTO class
class TestDTO extends DataTransferObject
{
    public ?string $name = null;
    public ?int $age = null;
    public ?array $tags = null;
}

class DataTransferObjectTest extends TestCase
{
    /** @test */
    public function it_can_create_dto_from_array(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'tags' => ['developer', 'php'],
        ];

        $dto = TestDTO::from($data);

        $this->assertEquals('John Doe', $dto->name);
        $this->assertEquals(30, $dto->age);
        $this->assertEquals(['developer', 'php'], $dto->tags);
    }

    /** @test */
    public function it_can_convert_to_array(): void
    {
        $dto = new TestDTO([
            'name' => 'Jane Doe',
            'age' => 25,
            'tags' => ['designer', 'css'],
        ]);

        $result = $dto->toArray();

        $expected = [
            'name' => 'Jane Doe',
            'age' => 25,
            'tags' => ['designer', 'css'],
        ];

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_can_convert_to_json(): void
    {
        $dto = new TestDTO([
            'name' => 'Bob Smith',
            'age' => 35,
        ]);

        $json = $dto->toJson();
        $decoded = json_decode($json, true);

        $this->assertEquals('Bob Smith', $decoded['name']);
        $this->assertEquals(35, $decoded['age']);
    }

    /** @test */
    public function it_filters_null_values_in_to_array(): void
    {
        $dto = new TestDTO([
            'name' => 'Test User',
            'age' => null,
            'tags' => ['test'],
        ]);

        $result = $dto->toArray();

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('tags', $result);
        $this->assertArrayNotHasKey('age', $result);
    }

    /** @test */
    public function it_can_get_only_specified_properties(): void
    {
        $dto = new TestDTO([
            'name' => 'Test User',
            'age' => 30,
            'tags' => ['test'],
        ]);

        $result = $dto->only(['name', 'age']);

        $this->assertEquals([
            'name' => 'Test User',
            'age' => 30,
        ], $result);
    }

    /** @test */
    public function it_can_exclude_specified_properties(): void
    {
        $dto = new TestDTO([
            'name' => 'Test User',
            'age' => 30,
            'tags' => ['test'],
        ]);

        $result = $dto->except(['age']);

        $this->assertEquals([
            'name' => 'Test User',
            'tags' => ['test'],
        ], $result);
    }
}
