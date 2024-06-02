<?php

namespace Utopia\Tests;

use PHPUnit\Framework\TestCase;
use Utopia\Cache\Cache;

abstract class Base extends TestCase
{
    /**
     * @var Cache
     */
    protected static $cache = null;

    /**
     * @var string
     */
    protected $key = 'test-key-for-cache';

    /**
     * @var string
     */
    protected $data = 'test data string';

    /**
     * @var string[]
     */
    protected $dataArray = ['test', 'data', 'string'];

    /**
     * General tests
     * Can be overwritten in specific adapter if required, such as None Cache
     */
    public function testCacheSave(): void
    {
        // test $data array
        $result = self::$cache->save($this->key, $this->dataArray, $this->key);

        $this->assertEquals($this->dataArray, $result);

        // test $data string
        $result = self::$cache->save($this->key, $this->data, $this->key);

        $this->assertEquals($this->data, $result);
    }

    public function testNotEmptyCacheKey(): void
    {
        $data = self::$cache->load($this->key, 60 * 60 * 24 * 30 * 3 /* 3 months */, $this->key);

        $this->assertEquals($this->data, $data);
    }

    public function testCachePurge(): void
    {
        $data = self::$cache->load($this->key, 60 * 60 * 24 * 30 * 3 /* 3 months */, $this->key);

        $this->assertEquals($this->data, $data);

        $result = self::$cache->purge($this->key);

        $this->assertEquals(true, $result);

        $data = self::$cache->load($this->key, 60 * 60 * 24 * 30 * 3 /* 3 months */, $this->key);

        $this->assertEquals(false, $data);
    }

    public function testCaseInsensitivity(): void
    {
        // Ensure case in-sensitivity first (configured in adapter's setUp)
        $data = self::$cache->save('planet', 'Earth', 'planet');
        var_dump($data);
        $this->assertEquals('Earth', $data);

        $data = self::$cache->load('planet', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'planet');
        $this->assertEquals('Earth', $data);
        $data = self::$cache->load('PLANET', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'PLANET');
        $this->assertEquals('Earth', $data);
        $data = self::$cache->load('PlAnEt', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'PlAnEt');
        $this->assertEquals('Earth', $data);

        $result = self::$cache->purge('PLaNEt');
        $this->assertEquals(true, $result);

        $data = self::$cache->load('planet', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'planet');
        $this->assertEquals(false, $data);
        $data = self::$cache->load('PLANET', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'PLANET');
        $this->assertEquals(false, $data);

        // Test case sensitivity
        self::$cache::setCaseSensitivity(true);

        $data = self::$cache->save('color', 'pink', 'color');
        $this->assertEquals('pink', $data);
        $data = self::$cache->load('color', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'color');
        $this->assertEquals('pink', $data);
        $data = self::$cache->load('COLOR', 60 * 60 * 24 * 30 * 3 /* 3 months */, 'COLOR');
        $this->assertEquals(false, $data);
    }

    public function testPing(): void
    {
        $this->assertEquals(true, self::$cache->ping());
    }

    public function testFlush(): void
    {
        $result1 = self::$cache->save('x', 'x', 'x');
        $result2 = self::$cache->save('y', 'y', 'y');

        $this->assertEquals($result1, self::$cache->load('x', 100, 'x'));
        $this->assertEquals($result2, self::$cache->load('y', 100, 'y'));

        // test $data string
        $result = self::$cache->flush();

        $this->assertEquals(true, $result);
        $this->assertEquals(false, self::$cache->load('x', 100, 'x'));
        $this->assertEquals(false, self::$cache->load('y', 100, 'y'));
    }
}
