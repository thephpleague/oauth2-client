<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Tool;

use League\OAuth2\Client\Tool\ArrayAccessorTrait;
use PHPUnit\Framework\TestCase;

class ArrayAccessorTraitTest extends TestCase
{
    use ArrayAccessorTrait;

    public function testGetRootValue(): void
    {
        $array = ['foo' => 'bar'];

        $result = $this->getValueByKey($array, 'foo');

        $this->assertEquals($array['foo'], $result);
    }

    public function testGetNonExistentValueWithDefault(): void
    {
        $array = [];
        $default = 'foo';

        $result = $this->getValueByKey($array, 'bar', $default);

        $this->assertEquals($default, $result);
    }

    public function testGetNestedValue(): void
    {
        $array = ['foo' => ['bar' => 'murray']];

        $result = $this->getValueByKey($array, 'foo.bar');

        $this->assertEquals($array['foo']['bar'], $result);
    }

    public function testGetNonExistentRootValue(): void
    {
        $array = ['foo' => 'bar'];

        $result = $this->getValueByKey($array, 'foo.bar');

        $this->assertNull($result);
    }
}
