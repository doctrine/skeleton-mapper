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

/**
 * Class for creating object instances without
 * invoking the __construct method.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ObjectFactory
{
    /**
     * @var array
     */
    private $prototypes = array();

    /**
     * @var array
     */
    private $reflectionClasses = array();

    /**
     * @param string $className
     *
     * @return object
     */
    public function create($className)
    {
        if (!isset($this->prototypes[$className])) {
            if (PHP_VERSION_ID === 50429 || PHP_VERSION_ID === 50513 || PHP_VERSION_ID >= 50600) {
                $this->prototypes[$className] = $this->getReflectionClass($className)->newInstanceWithoutConstructor();
            } else {
                $this->prototypes[$className] = unserialize(sprintf('O:%d:"%s":0:{}', strlen($className), $className));
            }
        }

        return clone $this->prototypes[$className];
    }

    /**
     * @param string $className
     *
     * @return \ReflectionClass
     */
    private function getReflectionClass($className)
    {
        if (!isset($this->reflectionClasses[$className])) {
            $this->reflectionClasses[$className] = new \ReflectionClass($className);
        }

        return $this->reflectionClasses[$className];
    }
}
