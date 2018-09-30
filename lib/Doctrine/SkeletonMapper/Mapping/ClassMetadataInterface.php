<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as BaseClassMetadata;

/**
 * Interface for class metadata instances.
 */
interface ClassMetadataInterface extends BaseClassMetadata
{
    /**
     * @param mixed[] $identifier
     */
    public function setIdentifier(array $identifier) : void;

    /**
     * @param string[] $identifierFieldNames
     */
    public function setIdentifierFieldNames(array $identifierFieldNames) : void;

    /**
     * @param mixed[] $mapping
     */
    public function mapField(array $mapping) : void;

    /**
     * @return mixed[][]
     */
    public function getFieldMappings() : array;

    public function hasLifecycleCallbacks(string $eventName) : bool;

    /**
     * @param object       $object
     * @param mixed[]|null $arguments
     */
    public function invokeLifecycleCallbacks(string $event, $object, ?array $arguments = null) : void;

    public function addLifecycleCallback(string $callback, string $event) : void;
}
