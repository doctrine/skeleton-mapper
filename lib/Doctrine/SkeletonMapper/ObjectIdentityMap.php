<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface;

use function count;
use function serialize;

/**
 * Class for maintaining an object identity map.
 */
class ObjectIdentityMap
{
    /** @var object[][] */
    private $identityMap = [];

    /** @var ObjectRepositoryFactoryInterface */
    private $objectRepositoryFactory;

    public function __construct(ObjectRepositoryFactoryInterface $objectRepositoryFactory)
    {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
    }

    /** @param object $object */
    public function contains($object): bool
    {
        $className = $object::class;

        $objectIdentifier = $this->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);

        return isset($this->identityMap[$className][$serialized]);
    }

    /**
     * @param mixed[] $data
     * @psalm-param class-string<object> $className
     *
     * @return object|null
     */
    public function tryGetById(string $className, array $data)
    {
        $serialized = serialize($this->extractIdentifierFromData($className, $data));

        if (isset($this->identityMap[$className][$serialized])) {
            return $this->identityMap[$className][$serialized];
        }

        return null;
    }

    /**
     * @param object  $object
     * @param mixed[] $data
     */
    public function addToIdentityMap($object, array $data): void
    {
        $className = $object::class;

        if (! isset($this->identityMap[$className])) {
            $this->identityMap[$object::class] = [];
        }

        $serialized = serialize($this->getObjectIdentifier($object));

        $this->identityMap[$object::class][$serialized] = $object;
    }

    public function clear(string|null $objectName = null): void
    {
        if ($objectName !== null) {
            unset($this->identityMap[$objectName]);
        } else {
            $this->identityMap = [];
        }
    }

    /** @param object $object */
    public function detach($object): void
    {
        $objectIdentifier = $this->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);
        unset($this->identityMap[$object::class][$serialized]);
    }

    public function count(): int
    {
        return count($this->identityMap);
    }

    /**
     * @param object $object
     *
     * @return mixed[] $identifier
     */
    private function getObjectIdentifier($object): array
    {
        return $this->objectRepositoryFactory
            ->getRepository($object::class)
            ->getObjectIdentifier($object);
    }

    /**
     * @param mixed[] $data
     * @psalm-param class-string<object> $className
     *
     * @return mixed[]
     */
    private function extractIdentifierFromData(string $className, array $data): array
    {
        return $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifierFromData($data);
    }
}
