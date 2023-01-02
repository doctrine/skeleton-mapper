<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\DataSource;

interface DataSource
{
    /** @return mixed[][] */
    public function getSourceRows(): array;
}
