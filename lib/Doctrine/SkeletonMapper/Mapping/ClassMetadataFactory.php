<?php

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;

/**
 * Class responsible for retrieving ClassMetadata instances.
 */
class ClassMetadataFactory implements BaseClassMetadataFactory
{
    /**
     * @var ClassMetadataInstantiatorInterface
     */
    private $classMetadataInstantiator;

    /**
     * @var array
     */
    protected $classes = array();

    /**
     * @param ClassMetadataInstantiatorInterface $classMetadataInstantiator
     */
    public function __construct(ClassMetadataInstantiatorInterface $classMetadataInstantiator)
    {
        $this->classMetadataInstantiator = $classMetadataInstantiator;
    }

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
        if (!isset($this->classes[$className])) {
            $metadata = $this->classMetadataInstantiator->instantiate($className);

            if ($metadata->reflClass->implementsInterface('Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface')) {
                $className::loadMetadata($metadata);
            }

            $this->classes[$className] = $metadata;
        }

        return $this->classes[$className];
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
