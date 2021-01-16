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
use function call_user_func;
use function is_callable;

class Profile extends BaseObject
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var callable|Address */
    private $address;

    /**
     * Assign identifier to object.
     *
     * @param mixed[] $identifier
     */
    public function assignIdentifier(array $identifier): void
    {
        $this->id = $identifier['_id'];
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
        $metadata->mapField(['fieldName' => 'address']);
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

    public function getName(): string
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

    public function getAddress(): Address
    {
        if (is_callable($this->address)) {
            $this->address = call_user_func($this->address);
        }

        return $this->address;
    }

    public function setAddress(Address $address): void
    {
        if ($this->address === $address) {
            return;
        }

        $this->onPropertyChanged('address', $this->address, $address);
        $this->address = $address;
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    public function addressChanged(string $propName, $oldValue, $newValue): void
    {
        $this->onPropertyChanged('address', $this->address, $this->address);
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

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (
            ! isset($data['address1'])
            && ! isset($data['address2'])
            && ! isset($data['city'])
            && ! isset($data['state'])
            && ! isset($data['zip'])
        ) {
            return;
        }

        $profile       = $this;
        $this->address = static function () use ($data, $profile) {
            $address = new Address($profile);

            if (isset($data['address1'])) {
                $address->setAddress1($data['address1']);
            }

            if (isset($data['address2'])) {
                $address->setAddress2($data['address2']);
            }

            if (isset($data['city'])) {
                $address->setCity($data['city']);
            }

            if (isset($data['state'])) {
                $address->setState($data['state']);
            }

            if (isset($data['zip'])) {
                $address->setZip($data['zip']);
            }

            return $address;
        };
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

        $address = $changeSet['address'];

        if ($address !== null) {
            unset($changeSet['address']);

            $changeSet['address1'] = $address->getAddress1();
            $changeSet['address2'] = $address->getAddress2();
            $changeSet['city']     = $address->getCity();
            $changeSet['state']    = $address->getState();
            $changeSet['zip']      = $address->getZip();
        }

        return $changeSet;
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

        if ($this->address !== null) {
            $address = $this->getAddress();

            $changeSet['address1'] = $address->getAddress1();
            $changeSet['address2'] = $address->getAddress2();
            $changeSet['city']     = $address->getCity();
            $changeSet['state']    = $address->getState();
            $changeSet['zip']      = $address->getZip();
        }

        if ($this->id !== null) {
            $changeSet['_id'] = $this->id;
        }

        return $changeSet;
    }
}
