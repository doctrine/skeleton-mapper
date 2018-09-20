Introduction
============

The Doctrine SkeletonMapper is a skeleton object mapper where you are
100% responsible for implementing the guts of the persistence
operations. This means you write plain old PHP code for the data
repositories, object repositories, object hydrators and object
persisters.

Installation
============

.. code-block:: console

    composer require doctrine/skeleton-mapper

Interfaces
==========

The ``ObjectDataRepository`` interface is responsible for reading the the raw data.

.. code-block::

    namespace Doctrine\SkeletonMapper\DataRepository;

    interface ObjectDataRepositoryInterface
    {
        public function find($id);
        public function findAll();
        public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
        public function findOneBy(array $criteria)
    }

The ``ObjectHydrator`` interface is responsible for hydrating the raw data to an object:

.. code-block::

    namespace Doctrine\SkeletonMapper\Hydrator;

    interface ObjectHydratorInterface
    {
        public function hydrate($object, array $data);
    }

The ``ObjectRepository`` interface is responsible for reading objects:

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

The ``ObjectPersisterInterface`` interface is responsible for persisting the state of an object to the raw data source:

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
======================

Now lets put it all together with an example implementation:

Model
-----

.. code-block::

    class User implements HydratableInterface, IdentifiableInterface, LoadMetadataInterface, NotifyPropertyChanged, PersistableInterface
    {
        /** @var int */
        private $id;

        /** @var string */
        private $username;

        /** @var string */
        private $password;

        /** @var PropertyChangedListener[] */
        private $listeners = [];

        public function getId() : ?int
        {
            return $this->id;
        }

        public function setId(int $id) : void
        {
            $this->id = $id;

            $this->onPropertyChanged('id', $this->id, $id);
        }

        public function getUsername() : string
        {
            return $this->username;
        }

        public function setUsername(string $username) : void
        {
            $this->username = $username;

            $this->onPropertyChanged('username', $this->username, $username);
        }

        public function getPassword() : string
        {
            return $this->password;
        }

        public function setPassword(string $password) : void
        {
            $this->password = $password;

            $this->onPropertyChanged('password', $this->password, $password);
        }

        public function addPropertyChangedListener(PropertyChangedListener $listener) : void
        {
            $this->listeners[] = $listener;
        }

        /**
         * @param mixed $oldValue
         * @param mixed $newValue
         */
        protected function onPropertyChanged(string $propName, $oldValue, $newValue) : void
        {
            if ($this->listeners === []) {
                return;
            }

            foreach ($this->listeners as $listener) {
                $listener->propertyChanged($this, $propName, $oldValue, $newValue);
            }
        }

        public static function loadMetadata(ClassMetadataInterface $metadata) : void
        {
            $metadata->setIdentifier(['id']);
            $metadata->setIdentifierFieldNames(['id']);
            $metadata->mapField([
                'fieldName' => 'id',
            ]);
            $metadata->mapField(['fieldName' => 'username']);
            $metadata->mapField(['fieldName' => 'password']);
        }

        /**
         * @see HydratableInterface
         *
         * @param mixed[] $data
         */
        public function hydrate(array $data, ObjectManagerInterface $objectManager) : void
        {
            if (isset($data['id'])) {
                $this->id = $data['id'];
            }

            if (isset($data['username'])) {
                $this->username = $data['username'];
            }

            if (isset($data['password'])) {
                $this->password = $data['password'];
            }
        }

        /**
         * @see PersistableInterface
         *
         * @return mixed[]
         */
        public function preparePersistChangeSet() : array
        {
            $changeSet = [
                'username' => $this->username,
                'password' => $this->password,
            ];

            if ($this->id !== null) {
                $changeSet['id'] = $this->id;
            }

            return $changeSet;
        }

        /**
         * @see PersistableInterface
         *
         *
         * @return mixed[]
         */
        public function prepareUpdateChangeSet(ChangeSet $changeSet) : array
        {
            $changeSet = array_map(function (Change $change) {
                return $change->getNewValue();
            }, $changeSet->getChanges());

            $changeSet['id'] = $this->id;

            return $changeSet;
        }

        /**
         * Assign identifier to object.
         *
         * @param mixed[] $identifier
         */
        public function assignIdentifier(array $identifier) : void
        {
            $this->id = $identifier['id'];
        }
    }

Mapper Services
---------------

Create all the necessary services for the mapper:

.. code-block::

    use Doctrine\Common\Collections\ArrayCollection;
    use Doctrine\Common\EventManager;
    use Doctrine\Common\NotifyPropertyChanged;
    use Doctrine\Common\PropertyChangedListener;
    use Doctrine\SkeletonMapper\DataRepository\ArrayObjectDataRepository;
    use Doctrine\SkeletonMapper\Hydrator\BasicObjectHydrator;
    use Doctrine\SkeletonMapper\Hydrator\HydratableInterface;
    use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
    use Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory;
    use Doctrine\SkeletonMapper\Mapping\ClassMetadataInstantiator;
    use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
    use Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface;
    use Doctrine\SkeletonMapper\ObjectFactory;
    use Doctrine\SkeletonMapper\ObjectIdentityMap;
    use Doctrine\SkeletonMapper\ObjectManager;
    use Doctrine\SkeletonMapper\ObjectManagerInterface;
    use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
    use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory;
    use Doctrine\SkeletonMapper\Persister\ArrayObjectPersister;
    use Doctrine\SkeletonMapper\Persister\IdentifiableInterface;
    use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
    use Doctrine\SkeletonMapper\Persister\PersistableInterface;
    use Doctrine\SkeletonMapper\UnitOfWork\Change;
    use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;

    $eventManager            = new EventManager();
    $classMetadataFactory    = new ClassMetadataFactory(new ClassMetadataInstantiator());
    $objectFactory           = new ObjectFactory();
    $objectRepositoryFactory = new ObjectRepositoryFactory();
    $objectPersisterFactory  = new ObjectPersisterFactory();
    $objectIdentityMap       = new ObjectIdentityMap(
        $objectRepositoryFactory,
        $classMetadataFactory
    );

    $userClassMetadata = new ClassMetadata(User::class);
    $userClassMetadata->setIdentifier(['id']);
    $userClassMetadata->setIdentifierFieldNames(['id']);
    $userClassMetadata->mapField([
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
            'id' => 1,
            'username' => 'jwage',
            'password' => 'password',
        ],
        2 => [
            'id' => 2,
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
    $userPersister = new ArrayObjectPersister(
        $objectManager, $users, User::class
    );

    $objectRepositoryFactory->addObjectRepository(User::class, $userRepository);
    $objectPersisterFactory->addObjectPersister(User::class, $userPersister);

Manage User Instances
---------------------

Now you can manage ``User`` instances and they will be persisted to the
``ArrayCollection`` instance we created above:

.. code-block::

    // create and persist a new user
    $user = new User();
    $user->setId(3);
    $user->setUsername('ocramius');
    $user->setPassword('test');

    $objectManager->persist($user);
    $objectManager->flush();
    $objectManager->clear();

    print_r($users);

    $user = $objectManager->find(User::class, 3);

    // modify the user
    $user->setUsername('guilherme');

    $objectManager->flush();

    print_r($users);

    // remove the user
    $objectManager->remove($user);
    $objectManager->flush();

    print_r($users);

Of course if you want to be in complete control and implement custom
code for all the above interfaces you can do so. You could write and
read from a CSV file, an XML document or any data source you can
imagine.
