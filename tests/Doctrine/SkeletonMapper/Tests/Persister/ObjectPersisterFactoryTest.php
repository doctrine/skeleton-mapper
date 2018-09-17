<?php

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use PHPUnit\Framework\TestCase;

class ObjectPersisterFactoryTest extends TestCase
{
    private $factory;

    protected function setUp()
    {
        $this->factory = new ObjectPersisterFactory();
    }

    public function testPersisterFactory()
    {
        $persister = $this->getMockBuilder('Doctrine\SkeletonMapper\Persister\ObjectPersisterInterface')
            ->getMock();

        $this->factory->addObjectPersister('TestClassName', $persister);

        $this->assertSame($persister, $this->factory->getPersister('TestClassName'));
        $this->assertSame(array('TestClassName' => $persister), $this->factory->getPersisters());
    }
}
