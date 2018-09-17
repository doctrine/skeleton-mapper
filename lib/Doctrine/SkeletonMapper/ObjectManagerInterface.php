<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\Persistence\ObjectManager as BaseObjectManagerInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface;

/**
 * Interface that object managers must implement.
 */
interface ObjectManagerInterface extends BaseObjectManagerInterface
{
    /**
     * @param object $object
     */
    public function update($object) : void;

    /**
     * @param mixed[] $data
     *
     * @return object
     */
    public function getOrCreateObject(string $className, array $data);

    public function getUnitOfWork() : UnitOfWork;

    /**
     * @param string $className
     *
     * @return ObjectRepositoryInterface
     */
    public function getRepository($className);

    /**
     * @param string $className
     */
    public function getClassMetadata($className) : ClassMetadataInterface;
}
