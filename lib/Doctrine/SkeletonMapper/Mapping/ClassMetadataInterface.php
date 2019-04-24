<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata as BaseClassMetadata;

/**
 * Interface for class metadata instances.
 */
interface ClassMetadataInterface extends BaseClassMetadata
{
    /**
     * @param array<int, string> $identifier
     */
    public function setIdentifier(array $identifier) : void;

    /**
     * @param array<int, string> $identifierFieldNames
     */
    public function setIdentifierFieldNames(array $identifierFieldNames) : void;

    /**
     * @param array<string, mixed> $mapping
     */
    public function mapField(array $mapping) : void;

    /**
     * @return array<string, mixed[]>
     */
    public function getFieldMappings() : array;

    public function hasLifecycleCallbacks(string $eventName) : bool;

    /**
     * @param array<mixed, mixed> $arguments
     */
    public function invokeLifecycleCallbacks(string $event, object $object, ?array $arguments = null) : void;

    public function addLifecycleCallback(string $callback, string $event) : void;
}
