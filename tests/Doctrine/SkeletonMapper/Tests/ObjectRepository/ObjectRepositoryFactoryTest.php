<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\ObjectRepository;

use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryFactory;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/** @group unit */
class ObjectRepositoryFactoryTest extends TestCase
{
    private ObjectRepositoryFactory $factory;

    public function testAddObjectRepository(): void
    {
        $repository = $this->createMock(ObjectRepositoryInterface::class);

        $this->factory->addObjectRepository('TestClassName', $repository);

        self::assertSame($repository, $this->factory->getRepository('TestClassName'));
    }

    public function testGetRepositoryThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ObjectRepository with class name DoesNotExist was not found');

        $this->factory->getRepository('DoesNotExist');
    }

    protected function setUp(): void
    {
        $this->factory = new ObjectRepositoryFactory();
    }
}
