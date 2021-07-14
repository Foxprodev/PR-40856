<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
use Symfony\Component\HttpKernel\KernelEvents;

class DevCacheInvalidator implements EventSubscriberInterface
{
    private array $poolsMap = [];

    private array $timeMap = [];

    public function __construct(
        private CacheItemPoolInterface $pool,
        private Psr6CacheClearer $cacheClearer,
    )
    {
        $this->invalidate();
    }

    public function addResource(SelfCheckingResourceInterface $resource, string $poolKey)
    {
        if (!isset($this->poolsMap[$poolKey])) {
            $this->poolsMap[$poolKey] = [];
        }
        $this->timeMap[$poolKey] = time();
        $this->poolsMap[$poolKey][$resource->__toString()] = $resource;
    }

    private function invalidate()
    {
        if (empty($this->poolsMap)) {
            $poolsItem = $this->pool->getItem('pools');
            $timeItem = $this->pool->getItem('time');
            if ($poolsItem->isHit() && $timeItem->isHit()) {
                $this->poolsMap = $poolsItem->get();
                $this->timeMap = $timeItem->get();
            }
        }
        foreach ($this->poolsMap as $poolKey => $resources) {
            $this->invalidatePool($resources, $poolKey);
        }
    }

    public function commit()
    {
        $poolItem = $this->pool->getItem('pools');
        $poolItem->set($this->poolsMap);
        $timeItem = $this->pool->getItem('time');
        $timeItem->set($this->timeMap);
        $this->pool->save($poolItem);
        $this->pool->save($timeItem);
    }

    /**
     * @param SelfCheckingResourceInterface[] $resources
     * @param string                          $poolKey
     */
    private function invalidatePool(array $resources, string $poolKey)
    {
        foreach ($resources as $resource) {
            $time = $this->timeMap[$poolKey];
            if (empty($time) || !$resource->isFresh($time)) {
                $this->cacheClearer->clearPool($poolKey);
                break;
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'commit',
        ];
    }
}
