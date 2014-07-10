<?php
namespace Toper;

use Toper\Storage\StorageInterface;

class CachedHostPoolProvider implements HostPoolProviderInterface
{
    const INSTANCES_KEY = 'cached_instances';

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
     * @var HostPoolInterface
     */
    private $hostPool;

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
        if ($this->hostPool === null) {
            $this->readCacheFromStorage();
        }

        if ($this->hostPool === null) {
            $this->buildCache();
        }

        if ($this->createCacheTime + $this->lifeTime < $this->clock->getTime()) {
            $this->buildCache();
        }

        return $this->hostPool;
    }


    private function buildCache()
    {
        $this->createCacheTime = $this->clock->getTime();
        $this->hostPool = $this->hostPoolProvider->get();
    }

    private function readCacheFromStorage()
    {
        if ($this->storage->exists(self::INSTANCES_KEY)) {
            $instances = unserialize($this->storage->get(self::INSTANCES_KEY));
            return $this->storage->get(self::INSTANCES_KEY);
        }
    }
}