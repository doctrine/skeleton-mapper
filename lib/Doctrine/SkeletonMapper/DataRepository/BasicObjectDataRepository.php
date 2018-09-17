<?php

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

abstract class BasicObjectDataRepository extends ObjectDataRepository
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager
     * @param string                                          $className
     */
    public function __construct(ObjectManagerInterface $objectManager, $className = null)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * @return string $className
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    public function find($id)
    {
        $identifier = $this->getIdentifier();

        $identifierValues = is_array($id) ? $id : array($id);

        $criteria = array_combine($identifier, $identifierValues);

        return $this->findOneBy($criteria);
    }

    /**
     * @return array $identifier
     */
    protected function getIdentifier()
    {
        return $this->objectManager
            ->getClassMetadata($this->getClassName())
            ->getIdentifier();
    }

    /**
     * @param object $object
     *
     * @return array
     */
    protected function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getRepository($this->getClassName())
            ->getObjectIdentifier($object);
    }
}
