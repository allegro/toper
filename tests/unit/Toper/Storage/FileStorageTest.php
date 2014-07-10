<?php
namespace Toper\Storage;

class FileStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @return null
     */
    public function shouldSetValue()
    {
        $storage = new FileStorage('/');
        $value = "some value";
        $key = "test";
        $storage->set($key, $value);

        $this->assertSame($value, $storage->get($key));
    }

    /**
     * @test
     *
     * @return null
     */
    public function shouldValueExists()
    {
        $storage = new FileStorage('/');
        $value = "some value";
        $key = "test";
        $storage->set($key, $value);

        $this->assertTrue($storage->exists($key));
    }

    /**
     * @test
     *
     * @return null
     */
    public function shouldValueNotExists()
    {
        $storage = new FileStorage('/');
        $this->assertFalse($storage->exists("key"));
    }
}
