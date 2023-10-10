<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;

final readonly class EntityTracker
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private ObjectTracker $objectTracker,
    ) {
    }

    public function hasChanged(
        object $entity,
        string $property = null,
        mixed $oldValue = new AnyValue(),
        mixed $newValue = new AnyValue(),
    ): bool {
        $uow = $this->getUnitOfWork($entity);

        if ($uow->isScheduledForInsert($entity)) {
            return true;
        }

        $uow->computeChangeSets();
        $this->objectTracker->computeChangeSets($entity, $uow);

        if (null !== $property) {
            return $this->getChangeSet($entity, $property, $uow)->hasChangedFor($oldValue, $newValue);
        }

        return $uow->isScheduledForUpdate($entity) || $uow->isScheduledForDirtyCheck($entity);
    }

    public function getChangeSet(object $entity, string $property, UnitOfWork $uow = null): PropertyChangeSet
    {
        $uow ??= $this->getUnitOfWork($entity);
        $changeSet = $uow->getEntityChangeSet($entity);
        if (!isset($changeSet[$property])) {
            return new PropertyChangeSet($property);
        }

        return new PropertyChangeSet($property, ...$changeSet[$property]);
    }

    private function getUnitOfWork(object $entity): UnitOfWork
    {
        /** @var EntityManagerInterface $em */
        $em = $this->managerRegistry->getManagerForClass(ClassUtils::getClass($entity));

        return $em->getUnitOfWork();
    }
}
