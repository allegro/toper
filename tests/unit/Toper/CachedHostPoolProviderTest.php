<?php
namespace Toper;

use Toper\Storage\StorageInterface;

class CachedHostPoolProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     *
     * @return null
     */
    public function shouldReturnInstancePoolFromNativeProvider()
    {
        $hostPool = $this->createHostPool();
        $hostPoolProviderMock = $this->createHostPollProviderMock();
        $hostPoolProviderMock->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValue($hostPool));
        $storage = $this->createStorageMock();

        $cachedProvider = new CachedHostPoolProvider(
            $hostPoolProviderMock,
            $storage,
            new ClockStub(12354),
            100
        );

        $this->assertSame($hostPool->toArray(), $cachedProvider->get()->toArray());
    }

    /**
     * @test
     *
     * @return null
     */
    public function shouldCacheHostPool()
    {
        $hostPool = $this->createHostPool();
        $hostPoolProviderMock = $this->createHostPollProviderMock();
        $hostPoolProviderMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($hostPool));
        $storage = $this->createStorageMock();

        $cachedProvider = new CachedHostPoolProvider(
            $hostPoolProviderMock,
            $storage,
            new ClockStub(12354),
            100
        );

        $cachedProvider->get();
        $cachedProvider->get();
    }

    /**
     * @test
     *
     * @return null
     */
    public function shouldInvalidateCacheAfterLifeTime()
    {

        $hostPool = $this->createHostPool();
        $hostPoolProviderMock = $this->createHostPollProviderMock();
        $hostPoolProviderMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue($hostPool));
        $storage = $this->createStorageMock();

        $timestamp = 1000;
        $lifeTime = 10;
        $clock = new ClockStub($timestamp);
        $cachedProvider = new CachedHostPoolProvider(
            $hostPoolProviderMock,
            $storage,
            $clock,
            $lifeTime
        );

        $cachedProvider->get();
        $clock->setTime($timestamp + $lifeTime + 1);
        $cachedProvider->get();
    }

    /**
     * @return SimpleHostPool
     */
    private function createHostPool()
    {
        $hosts = array();
        return new SimpleHostPool($hosts);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | HostPoolProviderInterface
     */
    private function createHostPollProviderMock()
    {
        return $this->getMock('Toper\HostPoolProviderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | StorageInterface
     */
    private function createStorageMock()
    {
        return $this->getMock('Toper\Storage\StorageInterface');
    }
}
