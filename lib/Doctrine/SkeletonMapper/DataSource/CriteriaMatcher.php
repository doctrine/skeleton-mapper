<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataSource;

use function in_array;

class CriteriaMatcher
{
    /** @var mixed[] */
    private $criteria;

    /** @var mixed[] */
    private $row;

    /**
     * @param mixed[] $criteria
     * @param mixed[] $row
     */
    public function __construct(array $criteria, array $row)
    {
        $this->criteria = $criteria;
        $this->row      = $row;
    }

    public function matches() : bool
    {
        $matches = true;

        foreach ($this->criteria as $key => $value) {
            if ($this->criteriaElementMatches($key, $value)) {
                continue;
            }

            $matches = false;
        }

        return $matches;
    }

    /**
     * @param mixed $value
     */
    private function criteriaElementMatches(string $key, $value) : bool
    {
        if (isset($value['$contains'])) {
            if ($this->contains($key, $value)) {
                return true;
            }
        } elseif ($this->equals($key, $value)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed[] $value
     */
    private function contains(string $key, array $value) : bool
    {
        return isset($this->row[$key]) && in_array($value['$contains'], $this->row[$key], true);
    }

    /**
     * @param mixed $value
     */
    private function equals(string $key, $value) : bool
    {
        return isset($this->row[$key]) && $this->row[$key] === $value;
    }
}
