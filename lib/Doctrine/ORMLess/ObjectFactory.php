<?php

namespace Doctrine\ORMLess;

class ObjectFactory implements ObjectFactoryInterface
{
    /**
     * @var object
     */
    protected $prototype;

    /**
     * @var array
     */
    protected $reflectionClasses = array();

    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className)
    {
        if ($this->prototype === null) {
            if (PHP_VERSION_ID === 50429 || PHP_VERSION_ID === 50513 || PHP_VERSION_ID >= 50600) {
                $this->prototype = $this->getReflectionClass($className)->newInstanceWithoutConstructor();
            } else {
                $this->prototype = unserialize(sprintf('O:%d:"%s":0:{}', strlen($className), $className));
            }
        }

        return clone $this->prototype;
    }

    /**
     * @param string $className
     *
     * @return \ReflectionClass
     */
    protected function getReflectionClass($className)
    {
        if (!isset($this->reflectionClasses[$className])) {
            $this->reflectionClasses[$className] = new \ReflectionClass($className);
        }

        return $this->reflectionClasses[$className];
    }
}
