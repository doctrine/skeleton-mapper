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
    /** @var Instantiator */
    private $instantiator;

    public function __construct()
    {
        $this->instantiator = new Instantiator();
    }

    public function create(string $className) : object
    {
        return $this->instantiator->instantiate($className);
    }
}
