Doctrine SkeletonMapper
=======================

**THIS IS A PROTOTYPE**

[![Build Status](https://travis-ci.org/doctrine/skeleton-mapper.png)](https://travis-ci.org/doctrine/skeleton-mapper)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/doctrine/skeleton-mapper/badges/quality-score.png?s=7e0e1d4b5d7f6be61a3cd804dba556a0e4d1141d)](https://scrutinizer-ci.com/g/doctrine/skeleton-mapper/)
[![Code Coverage](https://scrutinizer-ci.com/g/doctrine/skeleton-mapper/badges/coverage.png?s=a02332bc4d6a32df3171f2ba714e4583a70c0154)](https://scrutinizer-ci.com/g/doctrine/skeleton-mapper/)
[![Latest Stable Version](https://poser.pugx.org/doctrine/skeleton-mapper/v/stable.png)](https://packagist.org/packages/doctrine/skeleton-mapper)
[![Total Downloads](https://poser.pugx.org/doctrine/skeleton-mapper/downloads.png)](https://packagist.org/packages/doctrine/skeleton-mapper)
[![Dependency Status](https://www.versioneye.com/php/jwage:purl/1.0.0/badge.png)](https://www.versioneye.com/php/jwage:purl/1.0.0)

The Doctrine SkeletonMapper is a skeleton object mapper where you are 100% responsible for implementing the guts of the persistence operations. This means you write plain old PHP code for the data repositories, object repositories, object hydrators and object persisters.

## Example Implementation

Model class:

```php
class User
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;
}
```

User data repository class that just stores the users in memory in an ArrayCollection instance:

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Repository\ObjectDataRepository;

class UserDataRepository extends ObjectDataRepository
{
    private $users;

    public function __construct(ArrayCollection $users)
    {
        $this->users = $users;
    }

    public function find($id)
    {
        return isset($this->users[$id]) ? $this->users[$id] : null;
    }

    public function findByObject($object)
    {
        return $this->find($object->id);
    }

    public function findAll()
    {
        return $this->users;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $users = array();

        foreach ($this->users as $user) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($user[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                $users[] = $user;
            }
        }

        return $users;
    }

    public function findOneBy(array $criteria)
    {
        foreach ($this->users as $user) {
            $matches = true;

            foreach ($criteria as $key => $value) {
                if ($user[$key] !== $value) {
                    $matches = false;
                }
            }

            if ($matches) {
                return $user;
            }
        }
    }
}
```

User hydrator:

```php
use Doctrine\SkeletonMapper\Hydrator\ObjectHydrator;

class UserHydrator extends ObjectHydrator
{
    public function hydrate($object, array $data)
    {
        if (isset($data['_id'])) {
            $object->id = (int) $data['_id'];
        }

        if (isset($data['username'])) {
            $object->username = (string) $data['username'];
        }

        if (isset($data['password'])) {
            $object->password = (string) $data['password'];
        }
    }
}
```

User persister:

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\Persister\ObjectPersister;

class UserPersister extends ObjectPersister
{
    private $users;

    public function __construct(
        ObjectIdentityMap $objectIdentityMap,
        ArrayCollection $users)
    {
        parent::__construct($objectIdentityMap);
        $this->users = $users;
    }

    public function getClassName()
    {
        return 'User';
    }

    public function persistObject($object)
    {
        $this->users[$object->id] = $this->prepareChangeSet($object);

        return $this->users[$object->id];
    }

    public function updateObject($object)
    {
        $this->users[$object->id] = $this->prepareChangeSet($object);

        return $this->users[$object->id];
    }

    public function removeObject($object)
    {
        unset($this->users[$object->id]);
    }

    public function prepareChangeSet($object, array $changeSet = array())
    {
        return array(
            '_id' => (int) $object->id,
            'username' => $object->username,
            'password' => $object->password,
        );
    }
}
```

User repository:

```php
use Doctrine\SkeletonMapper\Repository\ObjectRepository;

class UserRepository extends ObjectRepository
{
    public function getClassName()
    {
        return 'User';
    }

    public function getObjectIdentifier($object)
    {
        return array('_id' => (int) $object->id);
    }

    public function getObjectIdentifierFromData(array $data)
    {
        return array('_id' => (int) $data['_id']);
    }

    public function merge($object)
    {
        $user = $this->find($object->id);

        $user->username = $object->username;
        $user->password = $object->password;
    }
}
```

Now put it all together:

```php
$users = new ArrayCollection(array(
    1 => array(
        '_id' => 1,
        'username' => 'jwage',
        'password' => 'password',
    ),
    2 => array(
        '_id' => 2,
        'username' => 'romanb',
        'password' => 'password',
    ),
));

$eventManager = new \Doctrone\Common\EventManager();
$classMetadataFactory = new \Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory();
$objectFactory = new \Doctrine\SkeletonMapper\ObjectFactory();
$objectRepositoryFactory = new \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory();
$objectPersisterFactory = new \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory();
$objectIdentityMap = new \Doctrine\SkeletonMapper\ObjectIdentityMap(
    $objectRepositoryFactory, $classMetadataFactory
);

// user class metadata
$userClassMetadata = new \Doctrine\SkeletonMapper\Mapping\ClassMetadata('User');
$userClassMetadata->identifier = array('_id');
$userClassMetadata->identifierFieldNames = array('id');
$userClassMetadata->mapField(array(
    'name' => '_id',
    'fieldName' => 'id',
));
$userClassMetadata->mapField(array(
    'fieldName' => 'username',
));
$userClassMetadata->mapField(array(
    'fieldName' => 'password',
));

$classMetadataFactory->setMetadataFor('User', $userClassMetadata);

$objectManager = new \Doctrine\SkeletonMapper\ObjectManager(
    $objectRepositoryFactory,
    $objectPersisterFactory,
    $objectIdentityMap,
    $classMetadataFactory,
    $eventManager
);

// user data repo
$userDataRepository = new UserDataRepository($users);

// user hydrator
$userHydrator = new UserHydrator();

// user repo
$userRepository = new UserRepository(
    $objectManager,
    $userDataRepository,
    $objectFactory,
    $userHydrator,
    $eventManager
);
$objectRepositoryFactory->addObjectRepository('User', $userRepository);

// user persister
$userPersister = new UserPersister($users);
$objectPersisterFactory->addObjectPersister('User', $userPersister);
```

Now manage User instances:

```php
// create and persist a new user
$user = new User();
$user->id = 1;
$user->username = 'jwage';
$user->password = 'test';

$objectManager->persist($user);
$objectManager->flush();

// modify the user
$user->username = 'jonwage';

$objectManager->update($user);
$objectManager->flush();

// remove the user
$objectManager->remove($user);
$objectManager->flush();
```
