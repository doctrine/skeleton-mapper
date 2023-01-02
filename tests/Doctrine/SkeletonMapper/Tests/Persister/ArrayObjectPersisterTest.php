<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Persister;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\Persister\ArrayObjectPersister;
use Doctrine\SkeletonMapper\Persister\PersistableInterface;
use Doctrine\SkeletonMapper\UnitOfWork\Change;
use Doctrine\SkeletonMapper\UnitOfWork\ChangeSet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @group unit */
class ArrayObjectPersisterTest extends TestCase
{
    /** @var ObjectManagerInterface|MockObject */
    private $objectManager;

    /** @var ArrayCollection<int, array<string, mixed>> */
    private $objects;

    /** @var ArrayObjectPersister */
    private $persister;

    /** @phpstan-var class-string */
    private $testClassName = ArrayObjectPersisterTestModel::class;

    public function testPersistObject(): void
    {
        $object = new ArrayObjectPersisterTestModel();

        self::assertEquals(['username' => 'jwage', 'id' => 1], $this->persister->persistObject($object));
        self::assertEquals([1 => ['username' => 'jwage', 'id' => 1]], $this->objects->toArray());
    }

    public function testUpdateObject(): void
    {
        $this->objects[1] = [
            'id' => 1,
            'username' => 'jwage',
        ];

        $object = new ArrayObjectPersisterTestModel();

        $changeSet = new ChangeSet($object, [new Change('username', 'jwage', 'jonwage')]);

        $repository = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects(self::once())
            ->method('getObjectIdentifier')
            ->with($object)
            ->will(self::returnValue(['id' => 1]));

        $this->objectManager->expects(self::once())
            ->method('getRepository')
            ->with($this->testClassName)
            ->will(self::returnValue($repository));

        self::assertEquals(['username' => 'jonwage', 'id' => 1], $this->persister->updateObject($object, $changeSet));
        self::assertEquals([1 => ['username' => 'jonwage', 'id' => 1]], $this->objects->toArray());
    }

    public function testRemoveObject(): void
    {
        $this->objects[1] = [
            'id' => 1,
            'username' => 'jwage',
        ];

        $object = new ArrayObjectPersisterTestModel();

        $repository = $this->getMockBuilder('Doctrine\SkeletonMapper\ObjectRepository\ObjectRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects(self::once())
            ->method('getObjectIdentifier')
            ->with($object)
            ->will(self::returnValue(['id' => 1]));

        $this->objectManager->expects(self::once())
            ->method('getRepository')
            ->with($this->testClassName)
            ->will(self::returnValue($repository));

        $this->persister->removeObject($object);

        self::assertCount(0, $this->objects);
    }

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $classMetadata             = new ClassMetadata($this->testClassName);
        $classMetadata->identifier = ['id'];

        $this->objectManager->expects(self::any())
            ->method('getClassMetadata')
            ->with($this->testClassName)
            ->will(self::returnValue($classMetadata));

        $this->objects   = new ArrayCollection();
        $this->persister = new ArrayObjectPersister(
            $this->objectManager,
            $this->objects,
            $this->testClassName,
        );
    }
}

class ArrayObjectPersisterTestModel implements PersistableInterface
{
    /** @return string[] */
    public function preparePersistChangeSet(): array
    {
        return ['username' => 'jwage'];
    }

    /** @return string[] */
    public function prepareUpdateChangeSet(ChangeSet $changeSet): array
    {
        $changes = [];

        foreach ($changeSet->getChanges() as $change) {
            $changes[$change->getPropertyName()] = $change->getNewValue();
        }

        return $changes;
    }
}
