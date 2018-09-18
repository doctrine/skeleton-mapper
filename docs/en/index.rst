Doctrine SkeletonMapper
=======================

The Doctrine SkeletonMapper is a skeleton object mapper where you are
100% responsible for implementing the guts of the persistence
operations. This means you write plain old PHP code for the data
repositories, object repositories, object hydrators and object
persisters.

Interfaces
----------

ObjectDataRepository:

.. code-block::

    namespace Doctrine\SkeletonMapper\DataRepository;

    interface ObjectDataRepositoryInterface
    {
        public function find($id);
        public function findAll();
        public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
        public function findOneBy(array $criteria)
    }

ObjectHydrator:

.. code-block::

    namespace Doctrine\SkeletonMapper\Hydrator;

    interface ObjectHydratorInterface
    {
        public function hydrate($object, array $data);
    }

ObjectRepository:

.. code-block::

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

.. code-block::

    namespace Doctrine\SkeletonMapper\Persister;

    interface ObjectPersisterInterface
    {
        public function persistObject($object);
        public function updateObject($object);
        public function removeObject($object);
        public function preparePersistChangeSet($object);
        public function prepareUpdateChangeSet($object, array $changeSet = []);
    }

Example Implementation
----------------------

Now lets put it all together with an example implementation:

.. code-block::

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

Create all the necessary services for the mapper:

.. code-block::

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\EventManager;
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

    $userClassMetadata = new ClassMetadata(User::class);
    $userClassMetadata->setIdentifier(['_id']);
    $userClassMetadata->setIdentifierFieldNames(['id']);
    $userClassMetadata->mapField([
        'name' => '_id',
        'fieldName' => 'id',
    ]);
    $userClassMetadata->mapField([
        'fieldName' => 'username',
    ]);
    $userClassMetadata->mapField([
        'fieldName' => 'password',
    ]);

    $classMetadataFactory->setMetadataFor(User::class, $userClassMetadata);

    $objectManager = new ObjectManager(
        $objectRepositoryFactory,
        $objectPersisterFactory,
        $objectIdentityMap,
        $classMetadataFactory,
        $eventManager
    );

    $users = new ArrayCollection([
        1 => [
            '_id' => 1,
            'username' => 'jwage',
            'password' => 'password',
        ],
        2 => [
            '_id' => 2,
            'username' => 'romanb',
            'password' => 'password',
        ],
    ]);

    $userDataRepository = new ArrayObjectDataRepository(
        $objectManager, $users, User::class
    );
    $userHydrator = new BasicObjectHydrator($objectManager);
    $userRepository = new BasicObjectRepository(
        $objectManager,
        $userDataRepository,
        $objectFactory,
        $userHydrator,
        $eventManager,
        User::class
    );
    $userPersister = new BasicObjectPersister(
        $objectManager, $users, User::class
    );

    $objectRepositoryFactory->addObjectRepository(User::class, $userRepository);
    $objectPersisterFactory->addObjectPersister(User::class, $userPersister);

Now you can manage user instances and they will be persisted to the
``ArrayCollection`` instance we created above:

.. code-block::

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

Of course if you want to be in complete control and implement custom
code for all the above interfaces you can do so. You could write and
read from a CSV file, an XML document or any data source you can
imagine.
