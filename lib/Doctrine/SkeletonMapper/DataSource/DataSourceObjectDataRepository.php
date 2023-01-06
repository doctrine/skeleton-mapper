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
    /** @var mixed[][]|null */
    private array|null $sourceRows = null;

    public function __construct(
        ObjectManagerInterface $objectManager,
        private DataSource $dataSource,
        string $className,
    ) {
        parent::__construct($objectManager, $className);
    }

    /** @return mixed[][] */
    public function findAll(): array
    {
        return $this->getSourceRows();
    }

    /**
     * @param mixed[] $criteria
     * @param mixed[] $orderBy
     *
     * @return mixed[][]
     */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
    ): array {
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
     * @param mixed[] $criteria
     *
     * @return mixed[]|null
     */
    public function findOneBy(array $criteria): array|null
    {
        foreach ($this->getSourceRows() as $row) {
            if ($this->matches($criteria, $row)) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $criteria
     * @param mixed[] $row
     */
    private function matches(array $criteria, array $row): bool
    {
        return (new CriteriaMatcher($criteria, $row))->matches();
    }

    /**
     * @param mixed[][] $rows
     * @param string[]  $orderBy
     *
     * @return mixed[][] $rows
     */
    private function sort(array $rows, array $orderBy): array
    {
        usort($rows, new Sorter($orderBy));

        return $rows;
    }

    /**
     * @param mixed[][] $rows
     *
     * @return mixed[][] $rows
     */
    private function slice(array $rows, int|null $limit, int|null $offset): array
    {
        if ($limit === null) {
            $limit = count($rows);
        }

        if ($offset === null) {
            $offset = 0;
        }

        return array_slice($rows, $offset, $limit);
    }

    /** @return mixed[][] */
    private function getSourceRows(): array
    {
        if ($this->sourceRows === null) {
            $this->sourceRows = $this->dataSource->getSourceRows();
        }

        return $this->sourceRows;
    }
}
