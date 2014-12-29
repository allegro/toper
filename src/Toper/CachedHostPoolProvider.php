<?php
namespace Toper;

use Toper\Storage\StorageInterface;

class CachedHostPoolProvider implements HostPoolProviderInterface
{
    const INSTANCES_KEY = 'cached_instances';


    const CREATE_CACHE_TIME_KEY = 'cached_instances_lifetime';
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var HostPoolProviderInterface
     */
    private $hostPoolProvider;

    /**
     * @var ClockInterface
     */
    private $clock;

    /**
     * @var int
     */
    private $lifeTime;

    /**
     * @var string[]
     */
    private $hosts;

    /**
     * @var int
     */
    private $createCacheTime;

    /**
     * @param HostPoolProviderInterface $hostPoolProvider
     * @param StorageInterface $storage
     * @param ClockInterface $clock
     * @param int $lifeTime
     */
    public function __construct(
        HostPoolProviderInterface $hostPoolProvider,
        StorageInterface $storage,
        ClockInterface $clock,
        $lifeTime
    ) {
        $this->hostPoolProvider = $hostPoolProvider;
        $this->storage = $storage;
        $this->clock = $clock;
        $this->lifeTime = $lifeTime;
    }

    /**
     * @return HostPoolInterface
     */
    public function get()
    {
        if ($this->hosts === null) {
            $this->readCacheFromStorage();
        }

        if ($this->hosts === null) {
            $this->buildCache();
        }

        if ($this->createCacheTime + $this->lifeTime < $this->clock->getTime()) {
            $this->buildCache();
        }

        return new SimpleHostPool($this->hosts);
    }

    /**
     * @return null
     */
    private function buildCache()
    {
        $this->hosts = array();
        $hostPool = $this->hostPoolProvider->get();
        while ($hostPool->hasNext()) {
            $this->hosts[] = $hostPool->getNext();
        }

        $this->createCacheTime = $this->clock->getTime();
        $this->writeCacheToStorage();
    }

    /**
     * @return null
     */
    private function readCacheFromStorage()
    {
        if ($this->storage->exists(self::INSTANCES_KEY)) {
            $this->hosts = $this->storage->get(self::INSTANCES_KEY);
            $this->createCacheTime = $this->storage->get(self::CREATE_CACHE_TIME_KEY);
        }

        return null;
    }

    /**
     * @return null
     */
    private function writeCacheToStorage()
    {
        $this->storage->set(self::INSTANCES_KEY, $this->hosts);
        $this->storage->set(self::CREATE_CACHE_TIME_KEY, $this->createCacheTime);
        $this->storage->save();
    }
}
