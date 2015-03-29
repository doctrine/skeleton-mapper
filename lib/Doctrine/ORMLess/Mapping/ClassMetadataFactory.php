<?php

namespace Doctrine\ORMLess\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;

class ClassMetadataFactory implements BaseClassMetadataFactory
{
    /**
     * @var array
     */
    protected $classes = array();

    /**
     * Returns all mapped classes.
     *
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
        return $this->classes;
    }

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @return ClassMetadata
     */
    public function getMetadataFor($className)
    {
        return isset($this->classes[$className]) ? $this->classes[$className] : null;
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param string $className
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->classes[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * @param string        $className
     * @param ClassMetadata $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->classes[$className] = $class;
    }

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped directly or as a MappedSuperclass.
     *
     * @param string $className
     *
     * @return bool
     */
    public function isTransient($className)
    {
        return isset($this->classes[$className]);
    }
}
