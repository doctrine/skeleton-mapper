<?php

namespace Doctrine\SkeletonMapper;

class ObjectIdentityMap
{
    /**
     * @var array
     */
    private $identityMap = array();

    /**
     * @var \Doctrine\SkeletonMapper\ObjectRepositoryFactory
     */
    private $objectRepositoryFactory;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectRepositoryFactory $objectRepositoryFactory
     */
    public function __construct(ObjectRepositoryFactory $objectRepositoryFactory)
    {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function contains($object)
    {
        $className = get_class($object);

        $objectIdentifier = $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifier($object);

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

        $serialized = serialize($this->extractIdentifierFromData($className, $data));

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
        $className = get_class($object);

        $objectIdentifier = $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);
        unset($this->identityMap[$className][$serialized]);
    }

    /**
     * @param string $className
     * @param array  $data
     *
     * @return array $identifier
     */
    private function extractIdentifierFromData($className, array $data)
    {
        $identifierFieldNames = $this->objectRepositoryFactory
            ->getRepository($className)
            ->getIdentifierFieldNames();

        $identifier = array();
        foreach ($identifierFieldNames as $identifierFieldName) {
            $identifier[$identifierFieldName] = $data[$identifierFieldName];
        }

        return $identifier;
    }
}
