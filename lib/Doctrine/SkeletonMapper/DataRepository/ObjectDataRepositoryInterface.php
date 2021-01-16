<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataRepository;

use UnexpectedValueException;

/**
 * Interface that object data repositories must implement.
 */
interface ObjectDataRepositoryInterface
{
    /**
     * Finds an objects data by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return mixed[] The objects array of data.
     */
    public function find($id): ?array;

    /**
     * Finds all object data in the repository.
     *
     * @return mixed[][] The objects data.
     */
    public function findAll(): array;

    /**
     * Finds objects data by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param mixed[]      $criteria
     * @param mixed[]|null $orderBy
     *
     * @return mixed[][] The objects data.
     *
     * @throws UnexpectedValueException
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array;

    /**
     * Finds a single objects data by a set of criteria.
     *
     * @param mixed[] $criteria The criteria.
     *
     * @return mixed[] The objects array of data
     */
    public function findOneBy(array $criteria): ?array;
}
