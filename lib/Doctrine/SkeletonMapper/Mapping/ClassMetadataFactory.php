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

namespace Doctrine\SkeletonMapper\Mapping;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory as BaseClassMetadataFactory;

/**
 * Class responsible for retrieving ClassMetadata instances.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ClassMetadataFactory implements BaseClassMetadataFactory
{
    /**
     * @var array
     */
    protected $classes = array();

    /**
     * Returns all mapped classes.
     *
     * @return array The ClassMetadata instances of all mapped classes.
     */
    public function getAllMetadata()
    {
        return $this->classes;
    }

    /**
     * Gets the class metadata descriptor for a class.
     *
     * @param string $className The name of the class.
     *
     * @return ClassMetadata
     */
    public function getMetadataFor($className)
    {
        if (!isset($this->classes[$className])) {
            $metadata = $this->createClassMetadata($className);

            if ($metadata->reflClass->implementsInterface('Doctrine\SkeletonMapper\Mapping\LoadMetadataInterface')) {
                $className::loadMetadata($metadata);
            }

            $this->classes[$className] = $metadata;
        }

        return $this->classes[$className];
    }

    /**
     * Checks whether the factory has the metadata for a class loaded already.
     *
     * @param string $className
     *
     * @return bool TRUE if the metadata of the class in question is already loaded, FALSE otherwise.
     */
    public function hasMetadataFor($className)
    {
        return isset($this->classes[$className]);
    }

    /**
     * Sets the metadata descriptor for a specific class.
     *
     * @param string        $className
     * @param ClassMetadata $class
     */
    public function setMetadataFor($className, $class)
    {
        $this->classes[$className] = $class;
    }

    /**
     * Returns whether the class with the specified name should have its metadata loaded.
     * This is only the case if it is either mapped directly or as a MappedSuperclass.
     *
     * @param string $className
     *
     * @return bool
     */
    public function isTransient($className)
    {
        return isset($this->classes[$className]);
    }

    /**
     * @param string $className
     */
    private function createClassMetadata($className)
    {
        return new ClassMetadata($className);
    }
}
