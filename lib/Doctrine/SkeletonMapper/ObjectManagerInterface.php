<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager as BaseObjectManagerInterface;
use Doctrine\Persistence\ObjectRepository as BaseObjectRepository;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface;

/**
 * Interface that object managers must implement.
 */
interface ObjectManagerInterface extends BaseObjectManagerInterface
{
    public function update(object $object) : void;

    /**
     * @param array<string, mixed> $data
     */
    public function getOrCreateObject(string $className, array $data) : object;

    public function getUnitOfWork() : UnitOfWork;

    /**
     * @return ObjectRepositoryInterface
     */
    public function getRepository(string $className) : BaseObjectRepository;

    public function getClassMetadata(string $className) : ClassMetadata;
}
