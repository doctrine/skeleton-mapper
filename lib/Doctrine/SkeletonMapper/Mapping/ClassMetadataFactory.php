<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;
use Doctrine\Persistence\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;
use function array_values;

/**
 * Class responsible for retrieving ClassMetadata instances.
 */
class ClassMetadataFactory implements BaseClassMetadataFactory
{
    /** @var ClassMetadataInstantiatorInterface */
    private $classMetadataInstantiator;

    /** @var array<string, BaseClassMetadata> */
    private $classes = [];

    public function __construct(ClassMetadataInstantiatorInterface $classMetadataInstantiator)
    {
        $this->classMetadataInstantiator = $classMetadataInstantiator;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllMetadata() : array
    {
        return array_values($this->classes);
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFor(string $className) : BaseClassMetadata
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
    public function hasMetadataFor(string $className) : bool
    {
        return isset($this->classes[$className]);
    }

    /**
     * {@inheritDoc}
     */
    public function setMetadataFor(string $className, BaseClassMetadata $class) : void
    {
        $this->classes[$className] = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function isTransient(string $className) : bool
    {
        return isset($this->classes[$className]);
    }
}
