<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper;

use ReflectionClass;
use const PHP_VERSION_ID;
use function sprintf;
use function strlen;
use function unserialize;

/**
 * Class for creating object instances without
 * invoking the __construct method.
 */
class ObjectFactory
{
    /** @var object[] */
    private $prototypes = [];

    /** @var ReflectionClass[] */
    private $reflectionClasses = [];

    /**
     * @return object
     */
    public function create(string $className)
    {
        if (! isset($this->prototypes[$className])) {
            if ($this->isReflectionMethodAvailable()) {
                $this->prototypes[$className] = $this->getReflectionClass($className)
                    ->newInstanceWithoutConstructor();
            } else {
                $this->prototypes[$className] = unserialize(sprintf('O:%d:"%s":0:{}', strlen($className), $className));
            }
        }

        return clone $this->prototypes[$className];
    }

    protected function isReflectionMethodAvailable() : bool
    {
        return PHP_VERSION_ID === 50429 || PHP_VERSION_ID === 50513 || PHP_VERSION_ID >= 50600;
    }

    private function getReflectionClass(string $className) : ReflectionClass
    {
        if (! isset($this->reflectionClasses[$className])) {
            $this->reflectionClasses[$className] = new ReflectionClass($className);
        }

        return $this->reflectionClasses[$className];
    }
}
