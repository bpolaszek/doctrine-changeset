<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use Doctrine\ORM\UnitOfWork;

/**
 * @internal
 */
final readonly class EntityAwareTracker
{
    public function __construct(
        private EntityTracker $tracker,
        private object $entity,
    ) {
    }

    public function isAboutToBeInserted(): bool
    {
        return $this->tracker->isAboutToBeInserted($this->entity);
    }

    public function isAboutToBeUpdated(): bool
    {
        return $this->tracker->isAboutToBeUpdated($this->entity);
    }

    public function isAboutToBeRemoved(): bool
    {
        return $this->tracker->isAboutToBeRemoved($this->entity);
    }

    public function hasChanged(
        string $property = null,
        mixed $oldValue = new AnyValue(),
        mixed $newValue = new AnyValue(),
    ): bool {
        return $this->tracker->hasChanged($this->entity, $property, $oldValue, $newValue);
    }

    public function getChangeSet(string $property, UnitOfWork $uow = null): PropertyChangeSet
    {
        return $this->tracker->getChangeSet($this->entity, $property, $uow);
    }
}
