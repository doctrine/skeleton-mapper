<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataSource;

use Doctrine\SkeletonMapper\DataRepository\BasicObjectDataRepository;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use function array_slice;
use function count;
use function usort;

class DataSourceObjectDataRepository extends BasicObjectDataRepository
{
    /** @var DataSource */
    private $dataSource;

    /** @var array<int, array<string, mixed>>|null */
    private $sourceRows;

    public function __construct(
        ObjectManagerInterface $objectManager,
        DataSource $dataSource,
        string $className
    ) {
        parent::__construct($objectManager, $className);
        $this->dataSource = $dataSource;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll() : array
    {
        return $this->getSourceRows();
    }

    /**
     * @param array<string, mixed>  $criteria
     * @param array<string, string> $orderBy
     *
     * @return array<int, array<string, mixed>>
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ) : array {
        $rows = [];

        foreach ($this->getSourceRows() as $row) {
            if (! $this->matches($criteria, $row)) {
                continue;
            }

            $rows[] = $row;
        }

        if ($orderBy !== null && $orderBy !== []) {
            $rows = $this->sort($rows, $orderBy);
        }

        if ($limit !== null || $offset !== null) {
            return $this->slice($rows, $limit, $offset);
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<string, mixed>|null
     */
    public function findOneBy(array $criteria) : ?array
    {
        foreach ($this->getSourceRows() as $row) {
            if ($this->matches($criteria, $row)) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $criteria
     * @param array<string, mixed> $row
     */
    private function matches(array $criteria, array $row) : bool
    {
        return (new CriteriaMatcher($criteria, $row))->matches();
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, mixed>             $orderBy
     *
     * @return array<int, array<string, mixed>> $rows
     */
    private function sort(array $rows, array $orderBy) : array
    {
        usort($rows, new Sorter($orderBy));

        return $rows;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array<string, mixed>> $rows
     */
    private function slice(array $rows, ?int $limit, ?int $offset) : array
    {
        if ($limit === null) {
            $limit = count($rows);
        }

        if ($offset === null) {
            $offset = 0;
        }

        return array_slice($rows, $offset, $limit);
    }

    /**
     * {@inheritDoc}
     */
    private function getSourceRows() : array
    {
        if ($this->sourceRows === null) {
            $this->sourceRows = $this->dataSource->getSourceRows();
        }

        return $this->sourceRows;
    }
}
