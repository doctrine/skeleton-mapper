<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface;
use function count;
use function get_class;
use function serialize;

/**
 * Class for maintaining an object identity map.
 */
class ObjectIdentityMap
{
    /** @var array<string, array<string, object>> */
    private $identityMap = [];

    /** @var ObjectRepositoryFactoryInterface */
    private $objectRepositoryFactory;

    public function __construct(ObjectRepositoryFactoryInterface $objectRepositoryFactory)
    {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
    }

    public function contains(object $object) : bool
    {
        $className = get_class($object);

        $objectIdentifier = $this->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);

        return isset($this->identityMap[$className][$serialized]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function tryGetById(string $className, array $data) : ?object
    {
        $serialized = serialize($this->extractIdentifierFromData($className, $data));

        if (isset($this->identityMap[$className][$serialized])) {
            return $this->identityMap[$className][$serialized];
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addToIdentityMap(object $object, array $data) : void
    {
        $className = get_class($object);

        if (! isset($this->identityMap[$className])) {
            $this->identityMap[get_class($object)] = [];
        }

        $serialized = serialize($this->getObjectIdentifier($object));

        $this->identityMap[get_class($object)][$serialized] = $object;
    }

    public function clear(?string $objectName = null) : void
    {
        if ($objectName !== null) {
            unset($this->identityMap[$objectName]);
        } else {
            $this->identityMap = [];
        }
    }

    public function detach(object $object) : void
    {
        $objectIdentifier = $this->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);
        unset($this->identityMap[get_class($object)][$serialized]);
    }

    public function count() : int
    {
        return count($this->identityMap);
    }

    /**
     * @return array<string, mixed>
     */
    private function getObjectIdentifier(object $object) : array
    {
        return $this->objectRepositoryFactory
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function extractIdentifierFromData(string $className, array $data) : array
    {
        return $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifierFromData($data);
    }
}
