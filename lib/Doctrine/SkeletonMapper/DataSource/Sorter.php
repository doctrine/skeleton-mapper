<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataSource;

use InvalidArgumentException;
use function count;
use function is_string;
use function sprintf;
use function strtolower;

class Sorter
{
    private const ORDER_ASC  = 'asc';
    private const ORDER_DESC = 'desc';

    /** @var int */
    private $level = 0;

    /** @var array<int, string> */
    private $fields;

    /** @var array<int, int> */
    private $orders;

    /**
     * @param array<string, string> $orderBy
     */
    public function __construct(array $orderBy)
    {
        if ($orderBy === []) {
            throw new InvalidArgumentException('The Sorter class does not accept an empty $orderBy');
        }

        foreach ($orderBy as $field => $order) {
            $this->fields[] = $field;
            $this->orders[] = $this->getOrder($order);
        }
    }

    /**
     * @param mixed[] $a
     * @param mixed[] $b
     */
    public function __invoke(array $a, array $b) : int
    {
        $returnVal        = 0;
        $comparisonField  = $this->fields[$this->level];
        $order            = $this->orders[$this->level];
        $aComparisonField = $this->getComparisonField($a, $comparisonField);
        $bComparisonField = $this->getComparisonField($b, $comparisonField);

        $comparisonResult = $aComparisonField <=> $bComparisonField;

        if ($comparisonResult !== 0) {
            $returnVal = $comparisonResult;
        } else {
            if ($this->level < count($this->fields) - 1) {
                $this->level++;

                return $this->__invoke($a, $b);
            }
        }

        $returnVal *= $order;

        $this->level = 0;

        return $returnVal;
    }

    private function getOrder(string $order) : int
    {
        $lowercaseOrder = strtolower($order);

        if ($lowercaseOrder === self::ORDER_ASC) {
            return 1;
        }

        if ($lowercaseOrder === self::ORDER_DESC) {
            return -1;
        }

        throw new InvalidArgumentException(sprintf(
            '$order value of %s is not accepted. Only a value of asc or desc is allowed.',
            $order
        ));
    }

    /**
     * @param mixed[] $item
     *
     * @return mixed
     */
    private function getComparisonField(array $item, string $field)
    {
        if (! isset($item[$field])) {
            throw new InvalidArgumentException(sprintf('Unable to find comparison field %s', $field));
        }

        $value = $item[$field];

        return is_string($value) ? strtolower($value) : $value;
    }
}
