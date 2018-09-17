<?php

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface;

/**
 * Class for maintaining an object identity map.
 */
class ObjectIdentityMap
{
    /**
     * @var array
     */
    private $identityMap = array();

    /**
     * @var \Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface
     */
    private $objectRepositoryFactory;

    /**
     * @var \Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory
     */
    private $classMetadataFactory;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactoryInterface $objectRepositoryFactory
     * @param \Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory                      $classMetadataFactory
     */
    public function __construct(
        ObjectRepositoryFactoryInterface $objectRepositoryFactory,
        ClassMetadataFactory $classMetadataFactory)
    {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function contains($object)
    {
        $className = get_class($object);

        $objectIdentifier = $this->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);

        return isset($this->identityMap[$className][$serialized]);
    }

    /**
     * @param string $className
     * @param array  $data
     *
     * @return object
     */
    public function tryGetById($className, array $data)
    {
        $serialized = serialize($this->extractIdentifierFromData($className, $data));

        if (isset($this->identityMap[$className][$serialized])) {
            return $this->identityMap[$className][$serialized];
        }
    }

    /**
     * @param object $object
     * @param array  $data
     */
    public function addToIdentityMap($object, array $data)
    {
        $className = get_class($object);

        if (!isset($this->identityMap[$className])) {
            $this->identityMap[get_class($object)] = array();
        }

        $serialized = serialize($this->getObjectIdentifier($object));

        $this->identityMap[get_class($object)][$serialized] = $object;
    }

    /**
     * @param string|null $objectName
     */
    public function clear($objectName = null)
    {
        if ($objectName !== null) {
            unset($this->identityMap[$objectName]);
        } else {
            $this->identityMap = array();
        }
    }

    /**
     * @param object $object
     */
    public function detach($object)
    {
        $objectIdentifier = $this->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);
        unset($this->identityMap[get_class($object)][$serialized]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->identityMap);
    }

    /**
     * @param object $object
     *
     * @return array $identifier
     */
    private function getObjectIdentifier($object)
    {
        return $this->objectRepositoryFactory
            ->getRepository(get_class($object))
            ->getObjectIdentifier($object);
    }

    /**
     * @param string $className
     * @param array  $data
     *
     * @return array $identifier
     */
    private function extractIdentifierFromData($className, array $data)
    {
        return $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifierFromData($data);
    }
}
