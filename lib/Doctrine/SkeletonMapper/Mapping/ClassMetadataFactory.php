<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;

/**
 * Class responsible for retrieving ClassMetadata instances.
 */
class ClassMetadataFactory implements BaseClassMetadataFactory
{
    /** @var ClassMetadataInstantiatorInterface */
    private $classMetadataInstantiator;

    /** @var ClassMetadataInterface[] */
    private $classes = [];

    public function __construct(ClassMetadataInstantiatorInterface $classMetadataInstantiator)
    {
        $this->classMetadataInstantiator = $classMetadataInstantiator;
    }

    /**
     * Returns all mapped classes.
     *
     * @return ClassMetadataInterface[] The ClassMetadataInterface instances of all mapped classes.
     */
    public function getAllMetadata(): array
    {
        return $this->classes;
    }

    /**
     * @param string $className
     */
    public function getMetadataFor($className): ClassMetadataInterface
    {
        if (! isset($this->classes[$className])) {
            $metadata = $this->classMetadataInstantiator->instantiate($className);

            if ($metadata->reflClass->implementsInterface('Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface')) {
                $className::loadMetadata($metadata);
            }

            $this->classes[$className] = $metadata;
        }

        return $this->classes[$className];
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetadataFor($className): bool
    {
        return isset($this->classes[$className]);
    }

    /**
     * @param string                 $className
     * @param ClassMetadataInterface $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->classes[$className] = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient($className): bool
    {
        return isset($this->classes[$className]);
    }
}
