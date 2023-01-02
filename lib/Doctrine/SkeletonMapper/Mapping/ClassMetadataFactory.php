<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;

/**
 * Class responsible for retrieving ClassMetadata instances.
 *
 * @template T of ClassMetadata
 * @template-implements BaseClassMetadataFactory<T>
 */
class ClassMetadataFactory implements BaseClassMetadataFactory
{
    /** @phpstan-var ClassMetadataInstantiatorInterface<object> */
    private $classMetadataInstantiator;

    /**
     * @var ClassMetadata[]
     * @psalm-var T[]
     */
    private $classes = [];

    /** @phpstan-param ClassMetadataInstantiatorInterface<object> $classMetadataInstantiator */
    public function __construct(ClassMetadataInstantiatorInterface $classMetadataInstantiator)
    {
        $this->classMetadataInstantiator = $classMetadataInstantiator;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllMetadata(): array
    {
        return $this->classes;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFor($className): ClassMetadata
    {
        if (! isset($this->classes[$className])) {
            /** @phpstan-var class-string<T> $className */
            $metadata = $this->classMetadataInstantiator->instantiate($className);

            if ($metadata->reflClass->implementsInterface('Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface')) {
                /** @phpstan-var string $className */
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
     * {@inheritDoc}
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
