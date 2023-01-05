<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use Doctrine\Instantiator\Instantiator;

/**
 * Class for creating object instances without
 * invoking the __construct method.
 */
class ObjectFactory
{
    private Instantiator $instantiator;

    public function __construct()
    {
        $this->instantiator = new Instantiator();
    }

    /** @phpstan-param class-string $className */
    public function create(string $className): object
    {
        return $this->instantiator->instantiate($className);
    }
}
