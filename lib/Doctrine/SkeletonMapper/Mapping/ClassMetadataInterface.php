<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Persistence\Mapping\ClassMetadata;

/**
 * Interface for class metadata instances.
 *
 * @template-covariant T of object
 * @template-extends ClassMetadata<T>
 */
interface ClassMetadataInterface extends ClassMetadata
{
    /** @param mixed[] $identifier */
    public function setIdentifier(array $identifier): void;

    /** @param string[] $identifierFieldNames */
    public function setIdentifierFieldNames(array $identifierFieldNames): void;

    /** @param mixed[] $mapping */
    public function mapField(array $mapping): void;

    /** @return mixed[][] */
    public function getFieldMappings(): array;

    public function hasLifecycleCallbacks(string $eventName): bool;

    /** @param mixed[]|null $arguments */
    public function invokeLifecycleCallbacks(string $event, object $object, array|null $arguments = null): void;

    public function addLifecycleCallback(string $callback, string $event): void;
}
