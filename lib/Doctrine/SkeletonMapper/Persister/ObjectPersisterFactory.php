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

namespace Doctrine\SkeletonMapper\Persister;

/**
 * Class responsible for retrieving ObjectPersister instances.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ObjectPersisterFactory
{
    /**
     * @var array
     */
    private $persisters = array();

    /**
     * @param string                                                $className
     * @param \Doctrine\Common\Persistence\ObjectPersisterInterface $objectPersister
     */
    public function addObjectPersister($className, ObjectPersisterInterface $objectPersister)
    {
        $this->persisters[$className] = $objectPersister;
    }

    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectPersisterInterface
     */
    public function getPersister($className)
    {
        if (!isset($this->persisters[$className])) {
            throw new \InvalidArgumentException(sprintf('ObjectPersister with class name %s was not found', $className));
        }

        return $this->persisters[$className];
    }

    /**
     * @return array
     */
    public function getPersisters()
    {
        return $this->persisters;
    }
}
