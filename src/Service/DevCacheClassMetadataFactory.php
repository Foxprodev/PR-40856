<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassResolverTrait;

class DevCacheClassMetadataFactory implements ClassMetadataFactoryInterface
{
    use ClassResolverTrait;

    private array $loadedClasses = [];


    public function __construct(
        private ClassMetadataFactoryInterface $decorated,
        private CacheItemPoolInterface $cacheItemPool,
        private DevCacheInvalidator $invalidator,
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFor($value)
    {

        $class = $this->getClass($value);

        if (isset($this->loadedClasses[$class])) {
            return $this->loadedClasses[$class];
        }

        $key = rawurlencode(strtr($class, '\\', '_'));

        $item = $this->cacheItemPool->getItem($key);
        if ($item->isHit()) {
            return $this->loadedClasses[$class] = $item->get();
        }

        $metadata = $this->decorated->getMetadataFor($value);
        $this->cacheItemPool->save($item->set($metadata));
        $this->addResource($metadata);

        return $this->loadedClasses[$class] = $metadata;
    }

    private function addResource($metadata)
    {
        if (isset($metadata)) {
            $reflection = $metadata->getReflectionClass();
            $dir = dirname($reflection->getFileName());
            $this->invalidator->addResource(new DirectoryResource($dir), 'cache.serializer');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasMetadataFor($value)
    {
        return $this->decorated->hasMetadataFor($value);
    }
}
