Doctrine SkeletonMapper
=======================

**THIS IS A PROTOTYPE**

[![Build Status](https://travis-ci.org/doctrine/skeleton-mapper.png)](https://travis-ci.org/doctrine/skeleton-mapper)

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
        if (isset($data['id'])) {
            $object->id = (int) $data['id'];
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
use Doctrine\SkeletonMapper\Persister\ObjectAction;
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
        $this->users[$object->id] = $this->objectToArray($object);

        return $this->users[$object->id];
    }

    public function updateObject($object)
    {
        $this->users[$object->id] = $this->objectToArray($object);

        return $this->users[$object->id];
    }

    public function removeObject($object)
    {
        unset($this->users[$object->id]);
    }

    public function executeObjectAction(ObjectAction $objectAction)
    {
        $object = $objectAction->getObject();
        $name = $objectAction->getName();
        $params = $objectAction->getParams();

        // do something

        $objectAction->setResult(array('success' => true));
    }

    public function objectToArray($object)
    {
        return array(
            'id' => $object->id,
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
        return array('id' => $object->id);
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
$classMetadataFactory = new \Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory();
$objectFactory = \Doctrine\SkeletonMapper\ObjectFactory();
$objectRepositoryFactory = new \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory();
$objectPersisterFactory = new \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory();
$objectIdentityMap = new \Doctrine\SkeletonMapper\ObjectIdentityMap(
    $objectRepositoryFactory, $classMetadataFactory
);

// user class metadata
$userClassMetadata = new \Doctrine\SkeletonMapper\Mapping\ClassMetadata('User');
$userClassMetadata->identifier = array('id');
$userClassMetadata->autoMapFields();

$classMetadataFactory->setMetadataFor('User', $userClassMetadata);

// user data store in memory
$users = new ArrayCollection(array(
    1 => array(
        'id' => 1,
        'username' => 'jwage',
        'password' => 'password',
    ),
    2 => array(
        'id' => 2,
        'username' => 'romanb',
        'password' => 'password',
    ),
));
$userDataRepository = new UserDataRepository($users);

// user hydrator
$userHydrator = new UserHydrator();

// user repo
$userRepository = new UserRepository(
    $userDataRepository,
    $objectFactory,
    $userHydrator,
    $objectIdentityMap
);
$objectRepositoryFactory->addObjectRepository('User', $userRepository);

// user persister
$userPersister = new UserPersister($objectIdentityMap, $users);
$objectPersisterFactory->addObjectPersister('User', $userPersister);

$unitOfWork = new \Doctrine\SkeletonMapper\UnitOfWork(
    $objectPersisterFactory,
    $objectRepositoryFactory,
    $objectIdentityMap
);

$objectManager = new \Doctrine\SkeletonMapper\ObjectManager(
    $objectRepositoryFactory,
    $objectPersisterFactory,
    $unitOfWork,
    $classMetadataFactory
);
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
