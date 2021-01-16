<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\ObjectRepository;

use Doctrine\Common\EventManager;
use Doctrine\SkeletonMapper\DataRepository\ObjectDataRepositoryInterface;
use Doctrine\SkeletonMapper\Hydrator\ObjectHydratorInterface;
use Doctrine\SkeletonMapper\Mapping\ClassMetadataInterface;
use Doctrine\SkeletonMapper\ObjectFactory;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use Doctrine\SkeletonMapper\ObjectRepository\ObjectRepository;
use Doctrine\SkeletonMapper\Tests\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group unit
 */
class ObjectRepositoryTest extends TestCase
{
    /** @var ObjectManagerInterface|MockObject */
    private $objectManager;

    /** @var ObjectDataRepositoryInterface|MockObject */
    private $objectDataRepository;

    /** @var ObjectFactory|MockObject */
    private $objectFactory;

    /** @var ObjectHydratorInterface|MockObject */
    private $hydrator;

    /** @var EventManager|MockObject */
    private $eventManager;

    /** @var ClassMetadataInterface|MockObject */
    private $classMetadata;

    /** @var TestObjectRepository */
    private $repository;

    public function testGetClassName(): void
    {
        self::assertEquals('TestClassName', $this->repository->getClassName());
    }

    public function testFind(): void
    {
        $data = ['username' => 'jwage'];

        $this->objectDataRepository->expects(self::once())
            ->method('find')
            ->with(1)
            ->will(self::returnValue($data));

        $this->objectManager->expects(self::once())
            ->method('getOrCreateObject')
            ->with('TestClassName', $data)
            ->will(self::returnValue(new stdClass()));

        $object = $this->repository->find(1);
        self::assertEquals(new stdClass(), $object);
    }

    public function testFindAll(): void
    {
        $data = [
            ['username' => 'jwage'],
            ['username' => 'romanb'],
        ];

        $object1 = new stdClass();
        $object2 = new stdClass();

        $this->objectDataRepository->expects(self::once())
            ->method('findAll')
            ->will(self::returnValue($data));

        $this->objectManager->expects(self::at(0))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[0])
            ->will(self::returnValue($object1));

        $this->objectManager->expects(self::at(1))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[1])
            ->will(self::returnValue($object2));

        $objects = $this->repository->findAll();
        self::assertSame([$object1, $object2], $objects);
    }

    public function testFindBy(): void
    {
        $data = [
            ['username' => 'jwage'],
            ['username' => 'romanb'],
        ];

        $object1 = new stdClass();
        $object2 = new stdClass();

        $this->objectDataRepository->expects(self::once())
            ->method('findBy')
            ->with([])
            ->will(self::returnValue($data));

        $this->objectManager->expects(self::at(0))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[0])
            ->will(self::returnValue($object1));

        $this->objectManager->expects(self::at(1))
            ->method('getOrCreateObject')
            ->with('TestClassName', $data[1])
            ->will(self::returnValue($object2));

        $objects = $this->repository->findBy([]);
        self::assertSame([$object1, $object2], $objects);
    }

    public function testFindOneBy(): void
    {
        $data     = ['username' => 'jwage'];
        $criteria = ['username' => 'jwage'];

        $this->objectDataRepository->expects(self::once())
            ->method('findOneBy')
            ->with($criteria)
            ->will(self::returnValue($data));

        $this->objectManager->expects(self::once())
            ->method('getOrCreateObject')
            ->with('TestClassName', $data)
            ->will(self::returnValue(new stdClass()));

        $object = $this->repository->findOneBy($criteria);
        self::assertEquals(new stdClass(), $object);
    }

    public function testRefresh(): void
    {
        $data = ['username' => 'jwage'];

        $this->objectDataRepository->expects(self::once())
            ->method('find')
            ->with(['id' => 1])
            ->will(self::returnValue($data));

        $object = new User();

        $this->hydrator->expects(self::once())
            ->method('hydrate')
            ->with($object, $data);

        $this->repository->refresh($object);
    }

    public function testCreate(): void
    {
        $object = new stdClass();

        $this->objectFactory->expects(self::once())
            ->method('create')
            ->with('stdClass')
            ->will(self::returnValue($object));

        self::assertSame($object, $this->repository->create('stdClass'));
    }

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->objectDataRepository = $this->createMock(ObjectDataRepositoryInterface::class);

        $this->objectFactory = $this->createMock(ObjectFactory::class);

        $this->hydrator = $this->createMock(ObjectHydratorInterface::class);

        $this->eventManager = $this->createMock(EventManager::class);

        $this->classMetadata = $this->createMock(ClassMetadataInterface::class);

        $this->objectManager->expects(self::once())
            ->method('getClassMetadata')
            ->with('TestClassName')
            ->will(self::returnValue($this->classMetadata));

        $this->repository = new TestObjectRepository(
            $this->objectManager,
            $this->objectDataRepository,
            $this->objectFactory,
            $this->hydrator,
            $this->eventManager,
            'TestClassName'
        );
    }
}

class TestObjectRepository extends ObjectRepository
{
    public function getClassMetadata(): ClassMetadataInterface
    {
        return $this->class;
    }

    /**
     * @param object $object
     *
     * @return int[]
     */
    public function getObjectIdentifier($object): array
    {
        return ['id' => 1];
    }

    /**
     * @param mixed[] $data
     *
     * @return int[]
     */
    public function getObjectIdentifierFromData(array $data): array
    {
        return ['id' => 1];
    }

    /**
     * @param object $object
     */
    public function merge($object): void
    {
    }
}
