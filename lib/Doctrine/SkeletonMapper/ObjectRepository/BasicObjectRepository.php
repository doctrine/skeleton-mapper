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

class BasicObjectRepository extends ObjectRepository
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var \Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface
     */
    protected $class;

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
        $this->class = $this->objectManager->getClassMetadata($this->className);
    }

    /**
     * Returns the objects identifier.
     *
     * @return array
     */
    public function getObjectIdentifier($object)
    {
        return $this->objectManager
            ->getClassMetadata(get_class($object))
            ->getIdentifierValues($object);
    }

    /**
     * Returns the identifier.
     *
     * @return array
     */
    public function getObjectIdentifierFromData(array $data)
    {
        $identifier = array();

        foreach ($this->class->identifier as $name) {
            $identifier[$name] = $data[$name];
        }

        return $identifier;
    }

    /**
     * @param object $object
     */
    public function merge($object)
    {
        throw new \BadMethodCallException('Not implemented.');
    }
}
