<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\SkeletonMapper\Mapping;

/**
 * Class used to hold metadata about mapped classes.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ClassMetadata implements ClassMetadataInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $identifier = array();

    /**
     * @var array
     */
    public $identifierFieldNames = array();

    /**
     * @var array
     */
    public $fieldMappings = array();

    /**
     * @var array
     */
    public $associationMappings = array();

    /**
     * The registered lifecycle callbacks for this class.
     *
     * @var array
     */
    public $lifecycleCallbacks = array();

    /**
     * @var \ReflectionClass
     */
    public $reflClass;

    /**
     * @var array
     */
    public $reflFields = array();

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->name = $className;
        $this->reflClass = new \ReflectionClass($className);
    }

    /**
     * @param array $mapping
     */
    public function mapField(array $mapping)
    {
        if (!isset($mapping['name'])) {
            $mapping['name'] = $mapping['fieldName'];
        }

        $this->fieldMappings[$mapping['fieldName']] = $mapping;

        if ($this->reflClass->hasProperty($mapping['fieldName'])) {
            $reflProp = $this->reflClass->getProperty($mapping['fieldName']);
            $reflProp->setAccessible(true);
            $this->reflFields[$mapping['fieldName']] = $reflProp;
        }
    }

    /**
     * Gets the fully-qualified class name of this persistent class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the mapped identifier field name.
     *
     * The returned structure is an array of the identifier field names.
     *
     * @return array
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Gets the ReflectionClass instance for this mapped class.
     *
     * @return \ReflectionClass
     */
    public function getReflectionClass()
    {
        return $this->reflClass;
    }

    /**
     * Checks if the given field name is a mapped identifier for this class.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function isIdentifier($fieldName)
    {
        return in_array($fieldName, $this->getIdentifierFieldNames());
    }

    /**
     * Checks if the given field is a mapped property for this class.
     *
     * @param string $fieldName
     *
     * @return bool
     */
    public function hasField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * A numerically indexed list of field names of this persistent class.
     *
     * This array includes identifier fields if present on this class.
     *
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->fieldMappings);
    }

    /**
     * An array of field mappings for this persistent class indexed by field name.
     *
     * @return array
     */
    public function getFieldMappings()
    {
        return $this->fieldMappings;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationNames()
    {
        return array_keys($this->associationMappings);
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeOfField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]['type']) ?
            $this->fieldMappings[$fieldName]['type'] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationTargetClass($assocName)
    {
        if (!isset($this->associationMappings[$assocName])) {
            throw new \InvalidArgumentException("Association name expected, '".$assocName."' is not an association.");
        }

        return $this->associationMappings[$assocName]['targetObject'];
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifierValues($object)
    {
        $identifier = array();
        foreach ($this->identifierFieldNames as $identifierFieldName) {
            $identifier[$identifierFieldName] = $this->reflFields[$identifierFieldName]->getValue($object);
        }

        return $identifier;
    }

    /**
     * {@inheritDoc}
     *
     * Checks whether the class has a mapped association (embed or reference) with the given field name.
     */
    public function hasAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]);
    }

    /**
     * {@inheritDoc}
     *
     * Checks whether the class has a mapped reference or embed for the specified field and
     * is a single valued association.
     */
    public function isSingleValuedAssociation($fieldName)
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
    public function isCollectionValuedAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]['type']) &&
            $this->associationMappings[$fieldName]['type'] === 'many';
    }

    /**
     * Dispatches the lifecycle event of the given object by invoking all
     * registered callbacks.
     *
     * @param string $event     Lifecycle event
     * @param object $object    Object on which the event occurred
     * @param array  $arguments Arguments to pass to all callbacks
     *
     * @throws \InvalidArgumentException if object is not this class or
     *                                   a Proxy of this class
     */
    public function invokeLifecycleCallbacks($event, $object, array $arguments = null)
    {
        if (!$object instanceof $this->name) {
            throw new \InvalidArgumentException(sprintf('Expected class "%s"; found: "%s"', $this->name, get_class($object)));
        }

        foreach ($this->lifecycleCallbacks[$event] as $callback) {
            if ($arguments !== null) {
                call_user_func_array(array($object, $callback), $arguments);
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
     * @return bool
     */
    public function hasLifecycleCallbacks($event)
    {
        return !empty($this->lifecycleCallbacks[$event]);
    }

    /**
     * Gets the registered lifecycle callbacks for an event.
     *
     * @param string $event
     *
     * @return array
     */
    public function getLifecycleCallbacks($event)
    {
        return isset($this->lifecycleCallbacks[$event]) ? $this->lifecycleCallbacks[$event] : array();
    }

    /**
     * Adds a lifecycle callback for objects of this class.
     *
     * If the callback is already registered, this is a NOOP.
     *
     * @param string $callback
     * @param string $event
     */
    public function addLifecycleCallback($callback, $event)
    {
        if (isset($this->lifecycleCallbacks[$event]) && in_array($callback, $this->lifecycleCallbacks[$event])) {
            return;
        }

        $this->lifecycleCallbacks[$event][] = $callback;
    }

    /**
     * Sets the lifecycle callbacks for objects of this class.
     *
     * Any previously registered callbacks are overwritten.
     *
     * @param array $callbacks
     */
    public function setLifecycleCallbacks(array $callbacks)
    {
        $this->lifecycleCallbacks = $callbacks;
    }

    /**
     * Returns an array of identifier field names numerically indexed.
     *
     * @return array
     */
    public function getIdentifierFieldNames()
    {
        return $this->identifierFieldNames;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMappedByTargetField($fieldName)
    {
        throw new \BadMethodCallException(__METHOD__.'() is not implemented yet.');
    }

    /**
     * {@inheritDoc}
     */
    public function isAssociationInverseSide($fieldName)
    {
        throw new \BadMethodCallException(__METHOD__.'() is not implemented yet.');
    }
}
