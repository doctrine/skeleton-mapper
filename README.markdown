Doctrine SkeletonMapper
=======================

**THIS IS A WORK IN PROGRESS BETA**

[![Build Status](https://travis-ci.org/doctrine/skeleton-mapper.png)](https://travis-ci.org/doctrine/skeleton-mapper)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/doctrine/skeleton-mapper/badges/quality-score.png?s=7e0e1d4b5d7f6be61a3cd804dba556a0e4d1141d)](https://scrutinizer-ci.com/g/doctrine/skeleton-mapper/)
[![Total Downloads](https://poser.pugx.org/doctrine/skeleton-mapper/downloads.png)](https://packagist.org/packages/doctrine/skeleton-mapper)

The Doctrine SkeletonMapper is a skeleton object mapper where you are 100% responsible for implementing the guts of the persistence operations. This means you write plain old PHP code for the data repositories, object repositories, object hydrators and object persisters.

## Interfaces

ObjectDataRepository:

```php
namespace Doctrine\SkeletonMapper\DataRepository;

interface ObjectDataRepositoryInterface
{
    public function find($id);
    public function findByObject($object);
    public function findAll();
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    public function findOneBy(array $criteria)
}
```

ObjectHydrator:

```php
namespace Doctrine\SkeletonMapper\Hydrator;

interface ObjectHydratorInterface
{
    public function hydrate($object, array $data);
}
```

ObjectRepository:

```php
namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Common\Persistence\ObjectRepository as BaseObjectRepositoryInterface;

interface ObjectRepositoryInterface extends BaseObjectRepositoryInterface
{
    public function getObjectIdentifier($object);
    public function getObjectIdentifierFromData(array $data);
    public function merge($object);
    public function hydrate($object, array $data);
    public function create($className);

    // inherited from Doctrine\Common\Persistence\ObjectRepository

    public function find($id);
    public function findAll();
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);
    public function findOneBy(array $criteria);
    public function getClassName();
}
```

ObjectPersister:

```php
namespace Doctrine\SkeletonMapper\Persister;

interface ObjectPersisterInterface
{
    public function persistObject($object);
    public function updateObject($object);
    public function removeObject($object);
    public function preparePersistChangeSet($object);
    public function prepareUpdateChangeSet($object, array $changeSet = array());
}
```

## Example Implementation

Now lets put it all together with an example implementation:

```php
namespace Model;

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

Create all the necessary services for the mapper:

```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrone\Common\EventManager;
use Doctrine\SkeletonMapper\DataRepository\ArrayObjectDataRepository;
use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectIdentityMap;
use Doctrine\SkeletonMapper\ObjectManager;
use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use Doctrine\SkeletonMapper\Persister\BasicObjectPersister;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory;

$eventManager            = new EventManager();
$classMetadataFactory    = new ClassMetadataFactory();
$objectFactory           = new ObjectFactory();
$objectRepositoryFactory = new ObjectRepositoryFactory();
$objectPersisterFactory  = new ObjectPersisterFactory();
$objectIdentityMap       = new ObjectIdentityMap(
    $objectRepositoryFactory,
    $classMetadataFactory
);

$userClassMetadata = new ClassMetadata('Model\User');
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

$classMetadataFactory->setMetadataFor('Model\User', $userClassMetadata);

$objectManager = new ObjectManager(
    $objectRepositoryFactory,
    $objectPersisterFactory,
    $objectIdentityMap,
    $classMetadataFactory,
    $eventManager
);

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

$userDataRepository = new ArrayObjectDataRepository(
    $objectManager, $users, 'Model\User'
);
$userHydrator = new BasicObjectHydrator($objectManager);
$userRepository = new BasicObjectRepository(
    $objectManager,
    $userDataRepository,
    $objectFactory,
    $userHydrator,
    $eventManager,
    'Model\User'
);
$userPersister = new BasicObjectPersister(
    $objectManager, $users, 'Model\User'
);

$objectRepositoryFactory->addObjectRepository('Model\User', $userRepository);
$objectPersisterFactory->addObjectPersister('Model\User', $userPersister);
```

Now you can manager user instances and they will be persisted to the `ArrayCollection` instance we created above:

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

If you want to store the users somewhere else you just need to swap out the `ObjectDataRepository` and `ObjectPersister`. Doctrine provides implementations for the Doctrine DBAL and MongoDB.

Here is an example using the DBAL:

```php
use Doctrine\SkeletonMapper\DataRepository\DBALObjectDataRepository;
use Doctrine\SkeletonMapper\DataRepository\DBALObjectPersister;

$userDataRepository = new DBALObjectDataRepository(
    $objectManager, $connection, 'Model\User', 'users'
);
$userPersister = new DBALObjectPersister(
    $objectManager, $connection, 'Model\User', 'users'
);
```

Of course if you want to be in complete control and implement custom code for all the above interfaces you can do so. You could write and read from a CSV file, or an XML document. You could even have the repositories and persisters read and write to a HTTP end point.
