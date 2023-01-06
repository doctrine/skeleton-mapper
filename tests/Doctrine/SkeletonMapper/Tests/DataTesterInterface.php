<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests;

interface DataTesterInterface
{
    /** @return mixed[] */
    public function find(int $id): array|null;

    public function set(int $id, string $key, mixed $value): void;

    public function count(): int;
}
