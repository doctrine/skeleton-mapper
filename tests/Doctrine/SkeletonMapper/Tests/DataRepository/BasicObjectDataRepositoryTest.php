<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\DataRepository;

use Doctrine\SkeletonMapper\DataRepository\BasicObjectDataRepository;
use Doctrine\SkeletonMapper\Mapping\ClassMetadata;
use Doctrine\SkeletonMapper\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @group unit */
class BasicObjectDataRepositoryTest extends TestCase
{
    private ObjectManagerInterface|MockObject $objectManager;

    private BasicObjectDataRepository $objectDataRepository;

    public function testGetClassName(): void
    {
        self::assertEquals('TestClassName', $this->objectDataRepository->getClassName());
    }

    public function testFind(): void
    {
        $class = $this->createMock(ClassMetadata::class);

        $class->expects(self::once())
            ->method('getIdentifier')
            ->will(self::returnValue(['_id' => 1]));

        $this->objectManager->expects(self::once())
            ->method('getClassMetadata')
            ->with('TestClassName')
            ->will(self::returnValue($class));

        self::assertEquals(['username' => 'jwage'], $this->objectDataRepository->find(1));
    }

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);

        $this->objectDataRepository = new TestBasicObjectDataRepository(
            $this->objectManager,
            'TestClassName',
        );
    }
}

class TestBasicObjectDataRepository extends BasicObjectDataRepository
{
    /** @return string[][] */
    public function findAll(): array
    {
        return [['username' => 'jwage']];
    }

    /**
     * @param mixed[]      $criteria
     * @param mixed[]|null $orderBy
     *
     * @return string[][]
     */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
    ): array {
        return [['username' => 'jwage']];
    }

    /**
     * @param mixed[] $criteria
     *
     * @return string[]
     */
    public function findOneBy(array $criteria): array
    {
        return ['username' => 'jwage'];
    }
}
