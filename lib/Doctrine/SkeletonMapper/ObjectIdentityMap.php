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

use Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory;

/**
 * Class for maintaining an object identity map.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ObjectIdentityMap
{
    /**
     * @var array
     */
    private $identityMap = array();

    /**
     * @var \Doctrine\SkeletonMapper\ObjectRepositoryFactory
     */
    private $objectRepositoryFactory;

    /**
     * @param \Doctrine\SkeletonMapper\Repository\ObjectRepositoryFactory $objectRepositoryFactory
     */
    public function __construct(ObjectRepositoryFactory $objectRepositoryFactory)
    {
        $this->objectRepositoryFactory = $objectRepositoryFactory;
    }

    /**
     * @param object $object
     *
     * @return bool
     */
    public function contains($object)
    {
        $className = get_class($object);

        $objectIdentifier = $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);

        return isset($this->identityMap[$className][$serialized]);
    }

    /**
     * @param string $className
     * @param array  $data
     *
     * @return object
     */
    public function tryGetById($className, array $data)
    {
        $serialized = serialize($this->extractIdentifierFromData($className, $data));

        if (isset($this->identityMap[$className][$serialized])) {
            return $this->identityMap[$className][$serialized];
        }
    }

    /**
     * @param object $object
     * @param array  $data
     */
    public function addToIdentityMap($object, array $data)
    {
        $className = get_class($object);

        if (!isset($this->identityMap[$className])) {
            $this->identityMap[get_class($object)] = array();
        }

        $serialized = serialize($this->extractIdentifierFromData($className, $data));

        $this->identityMap[get_class($object)][$serialized] = $object;
    }

    /**
     * @param string|null $objectName
     */
    public function clear($objectName = null)
    {
        if ($objectName !== null) {
            unset($this->identityMap[$objectName]);
        } else {
            $this->identityMap = array();
        }
    }

    /**
     * @param object $object
     */
    public function detach($object)
    {
        $className = get_class($object);

        $objectIdentifier = $this->objectRepositoryFactory
            ->getRepository($className)
            ->getObjectIdentifier($object);

        $serialized = serialize($objectIdentifier);
        unset($this->identityMap[$className][$serialized]);
    }

    /**
     * @param string $className
     * @param array  $data
     *
     * @return array $identifier
     */
    private function extractIdentifierFromData($className, array $data)
    {
        $identifierFieldNames = $this->objectRepositoryFactory
            ->getRepository($className)
            ->getIdentifierFieldNames();

        $identifier = array();
        foreach ($identifierFieldNames as $identifierFieldName) {
            $identifier[$identifierFieldName] = $data[$identifierFieldName];
        }

        return $identifier;
    }
}
