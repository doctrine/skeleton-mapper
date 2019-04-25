<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use function array_keys;
use function call_user_func_array;
use function get_class;
use function in_array;
use function sprintf;

/**
 * Class used to hold metadata about mapped classes.
 */
class ClassMetadata implements ClassMetadataInterface
{
    /** @var string */
    public $name;

    /** @var array<int, string> */
    public $identifier = [];

    /** @var array<int, string> */
    public $identifierFieldNames = [];

    /** @var array<string, array<string, mixed>> */
    public $fieldMappings = [];

    /** @var array<string, array<string, mixed>> */
    public $associationMappings = [];

    /** @var array<string, array<int, string>> */
    public $lifecycleCallbacks = [];

    /** @var ReflectionClass */
    public $reflClass;

    /** @var array<string, ReflectionProperty> */
    public $reflFields = [];

    public function __construct(string $className)
    {
        $this->name      = $className;
        $this->reflClass = new ReflectionClass($className);
    }

    /**
     * {@inheritDoc}
     */
    public function setIdentifier(array $identifier) : void
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function setIdentifierFieldNames(array $identifierFieldNames) : void
    {
        $this->identifierFieldNames = $identifierFieldNames;
    }

    /**
     * {@inheritDoc}
     */
    public function mapField(array $mapping) : void
    {
        if (! isset($mapping['name'])) {
            $mapping['name'] = $mapping['fieldName'];
        }

        if (isset($mapping['type']) && isset($mapping['targetObject'])) {
            $this->associationMappings[$mapping['fieldName']] = $mapping;
        } else {
            $this->fieldMappings[$mapping['fieldName']] = $mapping;
        }

        $this->initReflField($mapping);
    }

    /**
     * {@inheritDoc}
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier() : array
    {
        return $this->identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass() : ReflectionClass
    {
        return $this->reflClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier(string $fieldName) : bool
    {
        return in_array($fieldName, $this->getIdentifierFieldNames(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function hasField(string $fieldName) : bool
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldNames() : array
    {
        return array_keys($this->fieldMappings);
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldMappings() : array
    {
        return $this->fieldMappings;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames() : array
    {
        return array_keys($this->associationMappings);
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField(string $fieldName) : string
    {
        if (! isset($this->fieldMappings[$fieldName])) {
            throw new InvalidArgumentException(
                sprintf("Field name expected, '%s' is not an field.", $fieldName)
            );
        }

        return $this->fieldMappings[$fieldName]['type'] ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass(string $associationName) : string
    {
        if (! isset($this->associationMappings[$associationName])) {
            throw new InvalidArgumentException(
                sprintf("Association name expected, '%s' is not an association.", $associationName)
            );
        }

        return $this->associationMappings[$associationName]['targetObject'];
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues(object $object) : array
    {
        $identifier = [];
        foreach ($this->identifierFieldNames as $identifierFieldName) {
            $identifier[$this->fieldMappings[$identifierFieldName]['name']] = $this->reflFields[$identifierFieldName]->getValue($object);
        }

        return $identifier;
    }

    public function hasAssociation(string $fieldName) : bool
    {
        return isset($this->associationMappings[$fieldName]);
    }

    /**
     * {@inheritDoc}
     */
    public function isSingleValuedAssociation(string $fieldName) : bool
    {
        return isset($this->associationMappings[$fieldName]['type']) &&
            $this->associationMappings[$fieldName]['type'] === 'one';
    }

    /**
     * {@inheritDoc}
     */
    public function isCollectionValuedAssociation(string $fieldName) : bool
    {
        return isset($this->associationMappings[$fieldName]['type']) &&
            $this->associationMappings[$fieldName]['type'] === 'many';
    }

    /**
     * {@inheritDoc}
     */
    public function invokeLifecycleCallbacks(string $event, object $object, ?array $arguments = null) : void
    {
        if (! $object instanceof $this->name) {
            throw new InvalidArgumentException(
                sprintf('Expected class "%s"; found: "%s"', $this->name, get_class($object))
            );
        }

        foreach ($this->lifecycleCallbacks[$event] as $callback) {
            if ($arguments !== null) {
                /** @var callable $callable */
                $callable = [$object, $callback];

                call_user_func_array($callable, $arguments);
            } else {
                $object->$callback();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasLifecycleCallbacks(string $eventName) : bool
    {
        return isset($this->lifecycleCallbacks[$eventName]);
    }

    /**
     * Gets the registered lifecycle callbacks for an event.
     *
     * @return array<int, string>
     */
    public function getLifecycleCallbacks(string $event) : array
    {
        return $this->lifecycleCallbacks[$event] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function addLifecycleCallback(string $callback, string $event) : void
    {
        if (isset($this->lifecycleCallbacks[$event]) && in_array($callback, $this->lifecycleCallbacks[$event], true)) {
            return;
        }

        $this->lifecycleCallbacks[$event][] = $callback;
    }

    /**
     * Sets the lifecycle callbacks for objects of this class.
     *
     * Any previously registered callbacks are overwritten.
     *
     * @param array<string, array<int, string>> $callbacks
     */
    public function setLifecycleCallbacks(array $callbacks) : void
    {
        $this->lifecycleCallbacks = $callbacks;
    }

    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * {@inheritDoc}
     */
    public function getIdentifierFieldNames() : array
    {
        return $this->identifierFieldNames;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField(string $fieldName) : string
    {
        throw new BadMethodCallException(__METHOD__ . '() is not implemented yet.');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide(string $fieldName) : bool
    {
        throw new BadMethodCallException(__METHOD__ . '() is not implemented yet.');
    }

    /**
     * @param array<string, mixed> $mapping
     */
    private function initReflField(array $mapping) : void
    {
        if (! $this->reflClass->hasProperty($mapping['fieldName'])) {
            return;
        }

        $reflProp = $this->reflClass->getProperty($mapping['fieldName']);
        $reflProp->setAccessible(true);
        $this->reflFields[$mapping['fieldName']] = $reflProp;
    }
}
