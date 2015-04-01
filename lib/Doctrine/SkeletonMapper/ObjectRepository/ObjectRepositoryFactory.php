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

/**
 * Class responsible for retrieving ObjectRepository instances.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ObjectRepositoryFactory
{
    /**
     * @var array
     */
    private $repositories = array();

    /**
     * @param string                                                $className
     * @param \Doctrine\Common\Repository\ObjectRepositoryInterface $objectRepository
     */
    public function addObjectRepository($className, ObjectRepositoryInterface $objectRepository)
    {
        $this->repositories[$className] = $objectRepository;
    }

    /**
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        if (!isset($this->repositories[$className])) {
            throw new \InvalidArgumentException(sprintf('ObjectRepository with class name %s was not found', $className));
        }

        return $this->repositories[$className];
    }
}
