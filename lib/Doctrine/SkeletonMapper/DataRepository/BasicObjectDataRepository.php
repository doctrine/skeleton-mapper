<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataRepository;

use Doctrine\SkeletonMapper\ObjectManagerInterface;
use function array_combine;
use function is_array;

abstract class BasicObjectDataRepository extends ObjectDataRepository
{
    /** @var ObjectManagerInterface */
    protected $objectManager;

    /** @var string */
    protected $className;

    public function __construct(ObjectManagerInterface $objectManager, string $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    public function getClassName() : string
    {
        return $this->className;
    }

    public function setClassName(string $className) : void
    {
        $this->className = $className;
    }

    /**
     * @param mixed $id
     *
     * @return mixed[]
     */
    public function find($id) : ?array
    {
        $identifier = $this->getIdentifier();

        $identifierValues = is_array($id) ? $id : [$id];

        $criteria = array_combine($identifier, $identifierValues);

        return $this->findOneBy($criteria);
    }

    /**
     * @return mixed[]
     */
    protected function getIdentifier() : array
    {
        return $this->objectManager
            ->getClassMetadata($this->getClassName())
            ->getIdentifier();
    }

    /**
     * @param object $object
     *
     * @return mixed[]
     */
    protected function getObjectIdentifier($object) : array
    {
        return $this->objectManager
            ->getRepository($this->getClassName())
            ->getObjectIdentifier($object);
    }
}
