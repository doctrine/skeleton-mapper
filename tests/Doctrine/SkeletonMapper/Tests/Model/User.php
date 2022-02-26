<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\SkeletonMapper\Collections\LazyCollection;
use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

use function array_map;
use function call_user_func;
use function explode;
use function implode;
use function is_callable;

class User extends BaseObject
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var callable|Profile */
    private $profile;

    /** @var Collection<int, Group> */
    private $groups;

    /** @var string[] */
    public $called = [];

    public function __construct()
    {
        $this->groups = new ArrayCollection();
    }

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
        $metadata->mapField(['fieldName' => 'username']);
        $metadata->mapField(['fieldName' => 'password']);
        $metadata->mapField([
            'name' => 'profileId',
            'fieldName' => 'profile',
        ]);
        $metadata->mapField([
            'name' => 'groupIds',
            'fieldName' => 'groups',
        ]);
    }

    public function getId(): ?int
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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        if ($this->username === $username) {
            return;
        }

        $this->onPropertyChanged('username', $this->username, $username);
        $this->username = $username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        if ($this->password === $password) {
            return;
        }

        $this->onPropertyChanged('password', $this->password, $password);
        $this->password = $password;
    }

    public function getProfile(): Profile
    {
        if (is_callable($this->profile)) {
            $this->profile = call_user_func($this->profile);
        }

        return $this->profile;
    }

    public function setProfile(Profile $profile): void
    {
        if ($this->profile === $profile) {
            return;
        }

        $this->onPropertyChanged('profile', $this->profile, $profile);
        $this->profile = $profile;
    }

    public function addGroup(Group $group): void
    {
        $this->groups->add($group);
        $this->onPropertyChanged('groups', $this->groups, $this->groups);
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $method, array $arguments): void
    {
        $this->called[] = $method;
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

        if (isset($data['username'])) {
            $this->username = $data['username'];
        }

        if (isset($data['password'])) {
            $this->password = $data['password'];
        }

        if (isset($data['profileId']) && isset($data['profileName'])) {
            $profileData = [
                '_id' => $data['profileId'],
                'name' => $data['profileName'],
            ];

            $this->profile = static function () use ($objectManager, $profileData) {
                return $objectManager->getOrCreateObject(
                    'Doctrine\SkeletonMapper\Tests\Model\Profile',
                    $profileData
                );
            };
        } elseif (isset($data['profileId'])) {
            $this->profile = static function () use ($objectManager, $data) {
                return $objectManager->find(
                    Profile::class,
                    $data['profileId']
                );
            };
        }

        if (! isset($data['groupIds'])) {
            return;
        }

        $this->groups = new LazyCollection(static function () use ($objectManager, $data): ArrayCollection {
            return new ArrayCollection(array_map(static function (string $groupId) use ($objectManager): ?object {
                return $objectManager->find(
                    Group::class,
                    $groupId
                );
            }, explode(',', $data['groupIds'])));
        });
    }

    /**
     * @see PersistableInterface
     *
     * @return mixed[]
     */
    public function preparePersistChangeSet(): array
    {
        $changeSet = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        if ($this->profile !== null) {
            $changeSet['profileId'] = $this->getProfile()->getId();
        }

        $groupIds = $this->groups->map(static function (Group $group): int {
            return $group->getId();
        })->toArray();

        $changeSet['groupIds'] = implode(',', $groupIds);

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

        if (isset($changeSet['profile'])) {
            $changeSet['profileId'] = $changeSet['profile']->getId();
            unset($changeSet['profile']);
        }

        if (isset($changeSet['groups'])) {
            $groupIds = $changeSet['groups']->map(static function (Group $group): int {
                return $group->getId();
            })->toArray();

            $changeSet['groupIds'] = implode(',', $groupIds);
            unset($changeSet['groups']);
        }

        return $changeSet;
    }
}
