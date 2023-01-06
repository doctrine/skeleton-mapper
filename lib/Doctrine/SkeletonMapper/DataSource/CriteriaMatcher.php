<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataSource;

use function in_array;

class CriteriaMatcher
{
    /**
     * @param mixed[] $criteria
     * @param mixed[] $row
     */
    public function __construct(private array $criteria, private array $row)
    {
    }

    public function matches(): bool
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

    private function criteriaElementMatches(string $key, mixed $value): bool
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

    /** @param mixed[] $value */
    private function contains(string $key, array $value): bool
    {
        return isset($this->row[$key]) && in_array($value['$contains'], $this->row[$key], true);
    }

    private function equals(string $key, mixed $value): bool
    {
        return isset($this->row[$key]) && $this->row[$key] === $value;
    }
}
