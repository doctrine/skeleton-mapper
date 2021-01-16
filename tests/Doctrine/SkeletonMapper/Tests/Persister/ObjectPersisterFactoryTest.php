<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\SkeletonMapper\Persister\ObjectPersisterFactory;
use Doctrine\SkeletonMapper\Persister\ObjectPersisterInterface;
use PHPUnit\Framework\TestCase;

class ObjectPersisterFactoryTest extends TestCase
{
    /** @var ObjectPersisterFactory */
    private $factory;

    public function testPersisterFactory(): void
    {
        $persister = $this->createMock(ObjectPersisterInterface::class);

        $this->factory->addObjectPersister('TestClassName', $persister);

        self::assertSame($persister, $this->factory->getPersister('TestClassName'));
        self::assertSame(['TestClassName' => $persister], $this->factory->getPersisters());
    }

    protected function setUp(): void
    {
        $this->factory = new ObjectPersisterFactory();
    }
}
