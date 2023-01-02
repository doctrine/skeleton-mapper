<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\Persistence\ObjectManager as BaseObjectManagerInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface;

/**
 * Interface that object managers must implement.
 */
interface ObjectManagerInterface extends BaseObjectManagerInterface
{
    /** @param object $object */
    public function update($object): void;

    /**
     * @param mixed[] $data
     * @phpstan-param class-string<T> $className
     *
     * @return object|null
     * @psalm-return T|null
     *
     * @template T of object
     */
    public function getOrCreateObject(string $className, array $data);

    public function getUnitOfWork(): UnitOfWork;

    /**
     * @psalm-param class-string<object> $className
     *
     * @return ObjectRepositoryInterface<object>
     */
    public function getRepository($className);

    /**
     * @param class-string<T> $className
     *
     * @phpstan-return ClassMetadataInterface<T>
     *
     * @template T of object
     */
    public function getClassMetadata($className): ClassMetadataInterface;
}
