<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests;

interface DataTesterInterface
{
    /**
     * @return mixed[]
     */
    public function find(int $id) : ?array;

    /**
     * @param mixed $value
     */
    public function set(int $id, string $key, $value) : void;

    public function count() : int;
}
