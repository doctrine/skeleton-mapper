<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Persistence\ObjectRepository as BaseObjectRepositoryInterface;

/**
 * Interface that object repositories must implement.
 *
 * @template T of object
 * @template-extends BaseObjectRepositoryInterface<T>
 */
interface ObjectRepositoryInterface extends BaseObjectRepositoryInterface
{
    /**
     * Returns the objects identifier.
     *
     * @return mixed[]
     */
    public function getObjectIdentifier(object $object): array;

    /**
     * Returns the identifier.
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function getObjectIdentifierFromData(array $data): array;

    public function merge(object $object): void;

    /** @param mixed[] $data */
    public function hydrate(object $object, array $data): void;

    /** @phpstan-param class-string $className */
    public function create(string $className): object;

    public function refresh(object $object): void;
}
