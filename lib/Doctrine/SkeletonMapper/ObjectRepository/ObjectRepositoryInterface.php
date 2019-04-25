<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Persistence\ObjectRepository as BaseObjectRepositoryInterface;

/**
 * Interface that object repositories must implement.
 */
interface ObjectRepositoryInterface extends BaseObjectRepositoryInterface
{
    /**
     * Returns the objects identifier.
     *
     * @return array<string, mixed>
     */
    public function getObjectIdentifier(object $object) : array;

    /**
     * Returns the identifier.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function getObjectIdentifierFromData(array $data) : array;

    public function merge(object $object) : object;

    /**
     * @param array<string, mixed> $data
     */
    public function hydrate(object $object, array $data) : void;

    public function create(string $className) : object;

    public function refresh(object $object) : void;
}
