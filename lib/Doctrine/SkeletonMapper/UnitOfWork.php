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

use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterInterface;
use Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory;

/**
 * Class for managing the persistence of objects.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class UnitOfWork
{
    /**
     * @var \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory
     */
    private $objectPersisterFactory;

    /**
     * @var \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory
     */
    private $objectRepositoryFactory;

    /**
     * @var \Doctrine\SkeletonMapper\ObjectIdentityMap
     */
    private $objectIdentityMap;

    /**
     * @param \Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory   $objectPersisterFactory
     * @param \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory $objectRepositoryFactory
     * @param \Doctrine\SkeletonMapper\ObjectIdentityMap                  $objectIdentityMap
     */
    public function __construct(
        ObjectPersisterFactory $objectPersisterFactory,
        ObjectRepositoryFactory $objectRepositoryFactory,
        ObjectIdentityMap $objectIdentityMap)
    {
        $this->objectPersisterFactory = $objectPersisterFactory;
        $this->objectRepositoryFactory = $objectRepositoryFactory;
        $this->objectIdentityMap = $objectIdentityMap;
    }

    /**
     * @param object $object
     */
    public function merge($object)
    {
        $this->objectRepositoryFactory
            ->getRepository(get_class($object))
            ->merge($object);
    }

    /**
     * @param string|null $objectName
     */
    public function clear($objectName = null)
    {
        $this->objectIdentityMap->clear($objectName);

        $persisters = $this->objectPersisterFactory->getPersisters();

        $persistersToClear = array_filter($persisters, function (ObjectPersisterInterface $persister) use ($objectName) {
            return $objectName === null || $persister->getClassName() === $objectName;
        });

        foreach ($persistersToClear as $persister) {
            $persister->clear();
        }
    }

    /**
     * @param object $object
     */
    public function detach($object)
    {
        $this->objectIdentityMap->detach($object);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        $this->objectRepositoryFactory
            ->getRepository(get_class($object))
            ->refresh($object);
    }

    /**
     * @param object $object
     */
    public function contains($object)
    {
        return $this->objectIdentityMap->contains($object)
            || $this->objectPersisterFactory->getPersister(get_class($object))->isScheduledForPersist($object);
    }

    /**
     */
    public function commit()
    {
        $persisters = $this->objectPersisterFactory->getPersisters();

        foreach ($persisters as $persister) {
            $persister->commit();
        }
    }
}
