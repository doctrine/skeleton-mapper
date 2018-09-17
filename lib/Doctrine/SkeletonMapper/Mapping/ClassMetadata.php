<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

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

    /** @var mixed[] */
    public $identifier = [];

    /** @var string[] */
    public $identifierFieldNames = [];

    /** @var mixed[][] */
    public $fieldMappings = [];

    /** @var mixed[][] */
    public $associationMappings = [];

    /** @var string[][] */
    public $lifecycleCallbacks = [];

    /** @var ReflectionClass */
    public $reflClass;

    /** @var ReflectionProperty[] */
    public $reflFields = [];

    public function __construct(string $className)
    {
        $this->name      = $className;
        $this->reflClass = new ReflectionClass($className);
    }

    /**
     * @param mixed[] $identifier
     */
    public function setIdentifier(array $identifier) : void
    {
        $this->identifier = $identifier;
    }

    /**
     * @param string[] $identifierFieldNames
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
     * Gets the fully-qualified class name of this persistent class.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Gets the mapped identifier field name.
     *
     * The returned structure is an array of the identifier field names.
     *
     * @return mixed[]
     */
    public function getIdentifier() : array
    {
        return $this->identifier;
    }

    /**
     * Gets the ReflectionClass instance for this mapped class.
     */
    public function getReflectionClass() : ReflectionClass
    {
        return $this->reflClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isIdentifier($fieldName) : bool
    {
        return in_array($fieldName, $this->getIdentifierFieldNames(), true);
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($fieldName) : bool
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * A numerically indexed list of field names of this persistent class.
     *
     * This array includes identifier fields if present on this class.
     *
     * @return string[]
     */
    public function getFieldNames() : array
    {
        return array_keys($this->fieldMappings);
    }

    /**
     * An array of field mappings for this persistent class indexed by field name.
     *
     * @return mixed[][]
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
    public function getTypeOfField($fieldName) : ?string
    {
        return $this->fieldMappings[$fieldName]['type'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass($assocName) : string
    {
        if (! isset($this->associationMappings[$assocName])) {
            throw new InvalidArgumentException(
                sprintf("Association name expected, '%s' is not an association.", $assocName)
            );
        }

        return $this->associationMappings[$assocName]['targetObject'];
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object) : array
    {
        $identifier = [];
        foreach ($this->identifierFieldNames as $identifierFieldName) {
            $identifier[$this->fieldMappings[$identifierFieldName]['name']] = $this->reflFields[$identifierFieldName]->getValue($object);
        }

        return $identifier;
    }

    /**
     * {@inheritDoc}
     *
     * Checks whether the class has a mapped association (embed or reference) with the given field name.
     */
    public function hasAssociation($fieldName) : bool
    {
        return isset($this->associationMappings[$fieldName]);
    }

    /**
     * {@inheritDoc}
     *
     * Checks whether the class has a mapped reference or embed for the specified field and
     * is a single valued association.
     */
    public function isSingleValuedAssociation($fieldName) : bool
    {
        return isset($this->associationMappings[$fieldName]['type']) &&
            $this->associationMappings[$fieldName]['type'] === 'one';
    }

    /**
     * {@inheritDoc}
     *
     * Checks whether the class has a mapped reference or embed for the specified field and
     * is a collection valued association.
     */
    public function isCollectionValuedAssociation($fieldName) : bool
    {
        return isset($this->associationMappings[$fieldName]['type']) &&
            $this->associationMappings[$fieldName]['type'] === 'many';
    }

    /**
     * {@inheritDoc}
     */
    public function invokeLifecycleCallbacks(string $event, $object, ?array $arguments = null) : void
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
     * Checks whether the class has callbacks registered for a lifecycle event.
     *
     * @param string $event Lifecycle event
     *
     */
    public function hasLifecycleCallbacks(string $event) : bool
    {
        return isset($this->lifecycleCallbacks[$event]);
    }

    /**
     * Gets the registered lifecycle callbacks for an event.
     *
     *
     * @return string[]
     */
    public function getLifecycleCallbacks(string $event) : array
    {
        return $this->lifecycleCallbacks[$event] ?? [];
    }

    /**
     * Adds a lifecycle callback for objects of this class.
     *
     * If the callback is already registered, this is a NOOP.
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
     * @param string[][] $callbacks
     */
    public function setLifecycleCallbacks(array $callbacks) : void
    {
        $this->lifecycleCallbacks = $callbacks;
    }

    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * @return string[]
     */
    public function getIdentifierFieldNames() : array
    {
        return $this->identifierFieldNames;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($fieldName)
    {
        throw new \BadMethodCallException(__METHOD__ . '() is not implemented yet.');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($fieldName)
    {
        throw new \BadMethodCallException(__METHOD__ . '() is not implemented yet.');
    }

    /**
     * @param mixed[] $mapping
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
