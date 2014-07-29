<?php
namespace Toper;

use Toper\Storage\FileStorage;
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

        $providedHostPool = $cachedProvider->get();
        $this->assertSame($hostPool->toArray(), $providedHostPool->toArray());
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
     * @test
     *
     * @return null
     */
    public function shouldReadCacheFromStorage()
    {
        $instancesFromStorage = array("localhost.com");
        $hostPoolProviderMock = $this->createHostPollProviderMock();
        $hostPoolProviderMock->expects($this->never())
            ->method('get');

        $storage = $this->createStorageMock();
        $storage->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                    array(CachedHostPoolProvider::INSTANCES_KEY, $instancesFromStorage),
                    array(CachedHostPoolProvider::CREATE_CACHE_TIME_KEY, time())
                    )
                )
            );

        $storage->expects($this->any())
            ->method('exists')
            ->with(CachedHostPoolProvider::INSTANCES_KEY)
            ->will($this->returnValue(true));

        $cachedProvider = new CachedHostPoolProvider(
            $hostPoolProviderMock,
            $storage,
            new ClockStub(12354),
            100
        );

        $providedHostPool = $cachedProvider->get();
        $this->assertSame($instancesFromStorage, $providedHostPool->toArray());
    }

    /**
     * @test
     *
     * @return null
     */
    public function shouldReturnInstancesFromNativeProviderCacheFromStorageIsOutdated()
    {
        $lifeTime = 100;
        $hostPool = $this->createHostPool();

        $instancesFromStorage = array("localhost.com");
        $hostPoolProviderMock = $this->createHostPollProviderMock();
        $hostPoolProviderMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue($hostPool));

        $storage = $this->createStorageMock();
        $storage->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array(CachedHostPoolProvider::INSTANCES_KEY, $instancesFromStorage),
                        array(CachedHostPoolProvider::CREATE_CACHE_TIME_KEY, time() - $lifeTime - 2)
                    )
                )
            );

        $storage->expects($this->any())
            ->method('exists')
            ->with(CachedHostPoolProvider::INSTANCES_KEY)
            ->will($this->returnValue(true));

        $cachedProvider = new CachedHostPoolProvider(
            $hostPoolProviderMock,
            $storage,
            new ClockStub(time()),
            $lifeTime
        );

        $providedHostPool = $cachedProvider->get();
        $this->assertSame($hostPool->toArray(), $providedHostPool->toArray());
    }

    /**
     * @return SimpleHostPool
     */
    private function createHostPool()
    {
        $hosts = array("host.com.pl");
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
