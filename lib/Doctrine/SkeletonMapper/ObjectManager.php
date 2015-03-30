<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\SkeletonMapper;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory;
use Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory;
use Doctrine\SkeletonMapper\Persister\ObjectAction;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;

/**
 * Class for managing the persistence of objects.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ObjectManager implements ObjectManagerInterface
{
    /**
     * @var \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory
     */
    private $objectRepositoryFactory;

    /**
     * @var \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory
     */
    private $objectPersisterFactory;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectIdentityMap
     */
    private $objectIdentityMap;

    /**
     * @var \Doctrine\SkeletonMapper\UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var \Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory
     */
    private $metadataFactory;

    /**
     * @param \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory $objectRepositoryFactory
     * @param \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory   $objectPersisterFactory
     * @param \Doctrine\SkeletonMapper\ObjectIdentityMap                  $objectIdentityMap
     * @param \Doctrine\SkeletonMapper\Mapping\ClassMetadataFactory       $metadataFactory
     * @param \Doctrine\Common\EventManager                               $eventManager
     */
    public function __construct(
        ObjectRepositoryFactory $objectRepositoryFactory,
        ObjectPersisterFactory $objectPersisterFactory,
        ObjectIdentityMap $objectIdentityMap,
        ClassMetadataFactory $metadataFactory,
        EventManager $eventManager = null)
    {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
        $this->objectPersisterFactory = $objectPersisterFactory;
        $this->objectIdentityMap = $objectIdentityMap;
        $this->metadataFactory = $metadataFactory;
        $this->eventManager = $eventManager ?: new EventManager();

        $this->unitOfWork = new UnitOfWork(
            $this,
            $this->objectRepositoryFactory,
            $this->objectPersisterFactory,
            $this->objectIdentityMap,
            $this->eventManager
        );
    }

    /**
     * Finds an object by its identifier.
     *
     * This is just a convenient shortcut for getRepository($className)->find($id).
     *
     * @param string $className The class name of the object to find.
     * @param mixed  $id        The identity of the object to find.
     *
     * @return object The found object.
     */
    public function find($className, $id)
    {
        return $this->getRepository($className)->find($id);
    }

    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     */
    public function persist($object)
    {
        $this->unitOfWork->persist($object);
    }

    /**
     * Tells the ObjectManager to update the object on flush.
     *
     * The object will be updated in the database as a result of the flush operation.
     *
     * @param object $object The instance to update
     */
    public function update($object)
    {
        $this->unitOfWork->update($object);
    }

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object The object instance to remove.
     */
    public function remove($object)
    {
        $this->unitOfWork->remove($object);
    }

    /**
     * Tells the ObjectManager to execute the object action on flush.
     *
     * @param ObjectAction $objectAction The object instance to execute the action for.
     */
    public function action(ObjectAction $objectAction)
    {
        $this->unitOfWork->action($objectAction);
    }

    /**
     * Merges the state of a detached object into the persistence context
     * of this ObjectManager and returns the managed copy of the object.
     * The object passed to merge will not become associated/managed with this ObjectManager.
     *
     * @param object $object
     *
     * @return object
     */
    public function merge($object)
    {
        $this->unitOfWork->merge($object);
    }

    /**
     * Clears the ObjectManager. All objects that are currently managed
     * by this ObjectManager become detached.
     *
     * @param string|null $objectName if given, only objects of this type will get detached.
     */
    public function clear($objectName = null)
    {
        $this->unitOfWork->clear($objectName);
    }

    /**
     * Detaches an object from the ObjectManager, causing a managed object to
     * become detached. Unflushed changes made to the object if any
     * (including removal of the object), will not be synchronized to the database.
     * Objects which previously referenced the detached object will continue to
     * reference it.
     *
     * @param object $object The object to detach.
     */
    public function detach($object)
    {
        $this->unitOfWork->detach($object);
    }

    /**
     * Refreshes the persistent state of an object from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $object The object to refresh.
     */
    public function refresh($object)
    {
        $this->unitOfWork->refresh($object);
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     */
    public function flush()
    {
        $this->unitOfWork->commit();
    }

    /**
     * Gets the repository for a class.
     *
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        return $this->objectRepositoryFactory->getRepository($className);
    }

    /**
     * Returns the ClassMetadata descriptor for a class.
     *
     * The class name must be the fully-qualified class name without a leading backslash
     * (as it is returned by get_class($obj)).
     *
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata($className)
    {
        return $this->metadataFactory->getMetadataFor($className);
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * Helper method to initialize a lazy loading proxy or persistent collection.
     *
     * This method is a no-op for other objects.
     *
     * @param object $obj
     */
    public function initializeObject($obj)
    {
        throw new \BadMethodCallException('Not supported.');
    }

    /**
     * Checks if the object is part of the current UnitOfWork and therefore managed.
     *
     * @param object $object
     *
     * @return bool
     */
    public function contains($object)
    {
        return $this->unitOfWork->contains($object);
    }

    /**
     * @param array $data
     *
     * @return object
     */
    public function getOrCreateObject($className, array $data)
    {
        $object = $this->objectIdentityMap->tryGetById($className, $data);

        if (!$object) {
            $repository = $this->getRepository($className);

            $object = $repository->create($className);

            $repository->hydrate($object, $data);

            $this->objectIdentityMap->addToIdentityMap($object, $data);
        }

        return $object;
    }
}
