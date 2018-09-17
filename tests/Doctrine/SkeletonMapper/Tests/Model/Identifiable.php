<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Model;

interface Identifiable
{
    public function getId() : ?int;
}
