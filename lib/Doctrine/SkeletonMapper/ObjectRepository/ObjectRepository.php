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

namespace Doctrine\SkeletonMapper\ObjectRepository;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface;
use Doctrine\SkeletonMapper\Event\LifecycleEventArgs;
use Doctrine\SkeletonMapper\Event\PreLoadEventArgs;
use Doctrine\SkeletonMapper\Events;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface;

/**
 * Base class for object repositories to extend from.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
abstract class ObjectRepository implements ObjectRepositoryInterface
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface
     */
    protected $objectDataRepository;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface
     */
    protected $objectHydrator;

    /**
     * @var \Doctrine\Common\EventManager
     */
    protected $eventManager;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface                       $objectManager
     * @param \Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface $objectDataRepository
     * @param \Doctrine\SkeletonMapper\ObjectFactory                                $objectFactory
     * @param \Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface             $objectHydrator
     * @param \Doctrine\Common\EventManager                                         $eventManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ObjectDataRepositoryInterface $objectDataRepository,
        ObjectFactory $objectFactory,
        ObjectHydratorInterface $objectHydrator,
        EventManager $eventManager)
    {
        $this->objectManager = $objectManager;
        $this->objectDataRepository = $objectDataRepository;
        $this->objectFactory = $objectFactory;
        $this->objectHydrator = $objectHydrator;
        $this->eventManager = $eventManager;
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        $data = $this->objectDataRepository->find($id);

        if ($data === null) {
            return;
        }

        return $this->objectManager->getOrCreateObject($this->getClassName(), $data);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        $className = $this->getClassName();
        $objectsData = $this->objectDataRepository->findAll();

        $objects = array();
        foreach ($objectsData as $objectData) {
            $objects[] = $this->objectManager->getOrCreateObject($className, $objectData);
        }

        return $objects;
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $className = $this->getClassName();

        $objectsData = $this->objectDataRepository->findBy(
            $criteria, $orderBy, $limit, $offset
        );

        $objects = array();
        foreach ($objectsData as $objectData) {
            $objects[] = $this->objectManager->getOrCreateObject($className, $objectData);
        }

        return $objects;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $data = $this->objectDataRepository->findOneBy($criteria);

        if ($data === null) {
            return;
        }

        return $this->objectManager->getOrCreateObject($this->getClassName(), $data);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        $data = $this->objectDataRepository->findByObject($object);

        $this->hydrate($object, $data);
    }

    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data)
    {
        $className = get_class($object);
        $class = $this->objectManager->getClassMetadata($className);

        if (!empty($class->lifecycleCallbacks[Events::preLoad])) {
            $args = array(&$data);
            $class->invokeLifecycleCallbacks(Events::preLoad, $object, $args);
        }

        if ($this->eventManager->hasListeners(Events::preLoad)) {
            $this->eventManager->dispatchEvent(
                Events::preLoad,
                new PreLoadEventArgs($object, $this->objectManager, $data)
            );
        }

        $this->objectHydrator->hydrate($object, $data);

        if (!empty($class->lifecycleCallbacks[Events::postLoad])) {
            $class->invokeLifecycleCallbacks(Events::postLoad, $object);
        }

        if ($this->eventManager->hasListeners(Events::postLoad)) {
            $this->eventManager->dispatchEvent(
                Events::postLoad,
                new LifecycleEventArgs($object, $this->objectManager)
            );
        }
    }

    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className)
    {
        return $this->objectFactory->create($className);
    }
}
