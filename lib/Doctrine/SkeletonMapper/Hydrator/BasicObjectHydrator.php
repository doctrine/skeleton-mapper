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

namespace Doctrine\SkeletonMapper\Hydrator;

use Doctrine\SkeletonMapper\ObjectManagerInterface;

/**
 * Basic object hydrator that delegates hydration
 * to a method on the object that is being hydrated
 * or uses a dynamic hydration algorithm.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class BasicObjectHydrator extends ObjectHydrator
{
    /**
     * @var \Doctrine\SkeletonMapper\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Doctrine\SkeletonMapper\ObjectManagerInterface $objectManager $eventManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param object $object
     * @param array  $data
     */
    public function hydrate($object, array $data)
    {
        if ($object instanceof HydratableInterface) {
            $object->hydrate($data);
        } else {
            $this->abstractHydrate($object, $data);
        }
    }

    /**
     * @param object $object
     * @param array  $data
     */
    private function abstractHydrate($object, array $data)
    {
        $class = $this->objectManager->getClassMetadata(get_class($object));

        foreach ($class->fieldMappings as $fieldMapping) {
            if (!isset($data[$fieldMapping['name']])) {
                continue;
            }

            $class->reflFields[$fieldMapping['fieldName']]
                ->setValue($object, $data[$fieldMapping['name']]);
        }
    }
}
