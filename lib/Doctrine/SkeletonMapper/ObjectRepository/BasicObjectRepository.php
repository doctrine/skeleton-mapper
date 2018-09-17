<?php

namespace Doctrine\SkeletonMapper\ObjectRepository;

class BasicObjectRepository extends ObjectRepository
{
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
