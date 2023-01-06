<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

use function array_map;

class Group extends BaseObject
{
    private int|null $id = null;

    public function __construct(private string|null $name = null)
    {
    }

    /**
     * Assign identifier to object.
     *
     * @param mixed[] $identifier
     */
    public function assignIdentifier(array $identifier): void
    {
        $this->id = (int) $identifier['_id'];
    }

    public static function loadMetadata(ClassMetadataInterface $metadata): void
    {
        $metadata->setIdentifier(['_id']);
        $metadata->setIdentifierFieldNames(['id']);
        $metadata->mapField([
            'name' => '_id',
            'fieldName' => 'id',
        ]);
        $metadata->mapField(['fieldName' => 'name']);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        if ($this->id === $id) {
            return;
        }

        $this->onPropertyChanged('id', $this->id, $id);
        $this->id = $id;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        if ($this->name === $name) {
            return;
        }

        $this->onPropertyChanged('name', $this->name, $name);
        $this->name = $name;
    }

    /**
     * @see HydratableInterface
     *
     * @param mixed[] $data
     */
    public function hydrate(array $data, ObjectManagerInterface $objectManager): void
    {
        if (isset($data['_id'])) {
            $this->id = $data['_id'];
        }

        if (! isset($data['name'])) {
            return;
        }

        $this->name = $data['name'];
    }

    /**
     * @see PersistableInterface
     *
     * @return mixed[]
     */
    public function preparePersistChangeSet(): array
    {
        $changeSet = [
            'name' => $this->name,
        ];

        if ($this->id !== null) {
            $changeSet['_id'] = $this->id;
        }

        return $changeSet;
    }

    /**
     * @see PersistableInterface
     *
     * @return mixed[]
     */
    public function prepareUpdateChangeSet(ChangeSet $changeSet): array
    {
        $changeSet = array_map(static function (Change $change) {
            return $change->getNewValue();
        }, $changeSet->getChanges());

        $changeSet['_id'] = $this->id;

        return $changeSet;
    }
}
