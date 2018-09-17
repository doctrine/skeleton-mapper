<?php

namespace Doctrine\SkeletonMapper;

/**
 * Class for creating object instances without
 * invoking the __construct method.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ObjectFactory
{
    /**
     * @var array
     */
    private $prototypes = array();

    /**
     * @var array
     */
    private $reflectionClasses = array();

    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className)
    {
        if (!isset($this->prototypes[$className])) {
            if ($this->isReflectionMethodAvailable()) {
                $this->prototypes[$className] = $this->getReflectionClass($className)->newInstanceWithoutConstructor();
            } else {
                $this->prototypes[$className] = unserialize(sprintf('O:%d:"%s":0:{}', strlen($className), $className));
            }
        }

        return clone $this->prototypes[$className];
    }

    /**
     * @return boolean
     */
    protected function isReflectionMethodAvailable()
    {
        return PHP_VERSION_ID === 50429 || PHP_VERSION_ID === 50513 || PHP_VERSION_ID >= 50600;
    }

    /**
     * @param string $className
     *
     * @return \ReflectionClass
     */
    private function getReflectionClass($className)
    {
        if (!isset($this->reflectionClasses[$className])) {
            $this->reflectionClasses[$className] = new \ReflectionClass($className);
        }

        return $this->reflectionClasses[$className];
    }
}
