<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataSource;

interface DataSource
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSourceRows() : array;
}
