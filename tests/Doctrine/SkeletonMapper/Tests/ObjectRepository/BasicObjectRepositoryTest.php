<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\ObjectRepository;

use BadMethodCallException;
use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface;
use Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\ObjectRepository\BasicObjectRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/** @group unit */
class BasicObjectRepositoryTest extends TestCase
{
    private ObjectManagerInterface|MockObject $objectManager;

    private ObjectDataRepositoryInterface|MockObject $objectDataRepository;

    private ObjectFactory|MockObject $objectFactory;

    private ObjectHydratorInterface|MockObject $hydrator;

    private EventManager|MockObject $eventManager;

    /** @var ClassMetadata<object> */
    private ClassMetadata $classMetadata;

    private BasicObjectRepository $repository;

    /** @phpstan-var class-string */
    private $testClassName = BasicObjectRepositoryTestModel::class;

    public function testGetObjectIdentifier(): void
    {
        $object     = new BasicObjectRepositoryTestModel();
        $object->id = 1;

        $data = ['id' => 1];
        self::assertEquals($data, $this->repository->getObjectIdentifier($object));
    }

    public function testGetObjectIdentifierFromData(): void
    {
        $data = ['id' => 1];
        self::assertEquals($data, $this->repository->getObjectIdentifierFromData($data));
    }

    public function testMerge(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');

        $this->repository->merge(new stdClass());
    }

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->objectDataRepository = $this->createMock(ObjectDataRepositoryInterface::class);

        $this->objectFactory = $this->createMock(ObjectFactory::class);

        $this->hydrator = $this->createMock(ObjectHydratorInterface::class);

        $this->eventManager = $this->createMock(EventManager::class);

        $this->classMetadata                       = new ClassMetadata($this->testClassName);
        $this->classMetadata->identifier           = ['id'];
        $this->classMetadata->identifierFieldNames = ['id'];
        $this->classMetadata->mapField(['fieldName' => 'id']);

        $this->objectManager->expects(self::any())
            ->method('getClassMetadata')
            ->with($this->testClassName)
            ->will(self::returnValue($this->classMetadata));

        $this->repository = new BasicObjectRepository(
            $this->objectManager,
            $this->objectDataRepository,
            $this->objectFactory,
            $this->hydrator,
            $this->eventManager,
            $this->testClassName,
        );
    }
}

class BasicObjectRepositoryTestModel
{
    public int $id;
}
