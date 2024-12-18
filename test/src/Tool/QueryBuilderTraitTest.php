<?php

declare(strict_types=1);

namespace League\OAuth2\Client\Test\Tool;

use League\OAuth2\Client\Tool\QueryBuilderTrait;
use PHPUnit\Framework\TestCase;

use function ini_set;

class QueryBuilderTraitTest extends TestCase
{
    use QueryBuilderTrait;

    public function testBuildQueryString(): void
    {
        ini_set('arg_separator.output', '&amp;');

        $params = [
            'a' => 'foo',
            'b' => 'bar',
            'c' => '+',
        ];

        $query = $this->buildQueryString($params);

        $this->assertSame('a=foo&b=bar&c=%2B', $query);
    }
}
