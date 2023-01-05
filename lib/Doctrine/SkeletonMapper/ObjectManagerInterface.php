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
    public function update(object $object): void;

    /**
     * @param mixed[] $data
     * @phpstan-param class-string<T> $className
     *
     * @psalm-return T|null
     *
     * @template T of object
     */
    public function getOrCreateObject(string $className, array $data): object|null;

    public function getUnitOfWork(): UnitOfWork;

    /**
     * @psalm-param class-string<object> $className
     *
     * @return ObjectRepositoryInterface<object>
     */
    public function getRepository(string $className): ObjectRepositoryInterface;

    /**
     * @param class-string<T> $className
     *
     * @phpstan-return ClassMetadataInterface<T>
     *
     * @template T of object
     */
    public function getClassMetadata(string $className): ClassMetadataInterface;
}
