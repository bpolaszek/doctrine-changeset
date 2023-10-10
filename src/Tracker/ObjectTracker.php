<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionProperty;
use Symfony\Contracts\Service\ResetInterface;
use WeakMap;

use function array_keys;
use function is_object;

/**
 * @internal
 */
final class ObjectTracker implements ResetInterface
{
    /**
     * @var array<string, array<string, ReflectionProperty>>
     */
    private array $config;

    /**
     * @var WeakMap<object, array<string, object>>
     */
    private WeakMap $tracked;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
        $this->reset();
    }

    public function reset(): void
    {
        $this->tracked = new WeakMap();
    }

    public function postLoad(PostLoadEventArgs $eventArgs): void
    {
        $this->init();
        $entity = $eventArgs->getObject();
        $this->track($entity);
    }

    public function postFlush(PostFlushEventArgs $eventArgs): void
    {
        $this->init();
        $uow = $eventArgs->getObjectManager()->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $this->track($entity);
            }
        }
    }

    public function preFlush(PreFlushEventArgs $eventArgs): void
    {
        $this->init();
        $uow = $eventArgs->getObjectManager()->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $entities) {
            foreach ($entities as $entity) {
                $this->computeChangeSets($entity, $uow);
            }
        }
    }

    public function computeChangeSets(object $entity, UnitOfWork $uow): void
    {
        $class = ClassUtils::getClass($entity);
        foreach (array_keys($this->config[$class] ?? []) as $property) {
            if ($this->shouldTriggerChangeNotification($entity, $property)) {
                $reflProperty = $this->config[$class][$property];
                $value = $reflProperty->getValue($entity);
                if (is_object($value)) {
                    $value = clone $value;
                }
                $reflProperty->setValue($entity, $value);
                $oldValue = $this->tracked[$entity][$property];
                $uow->propertyChanged($entity, $property, $oldValue, $value);
                $uow->scheduleExtraUpdate($entity, [$property => [$oldValue, $value]]);
            }
        }
    }

    private function track(object $entity): void
    {
        $class = ClassUtils::getClass($entity);

        foreach ($this->config[$class] ?? [] as $property => $reflProperty) {
            $value = $reflProperty->getValue($entity);
            if (is_object($value)) {
                $value = clone $value;
            }
            $this->tracked[$entity] ??= [];
            $this->tracked[$entity][$property] = $value;
        }
    }

    private function shouldTriggerChangeNotification(object $entity, string $property): bool
    {
        $class = ClassUtils::getClass($entity);
        $reflProperty = $this->config[$class][$property] ?? null;
        if ($reflProperty instanceof ReflectionProperty && isset($this->tracked[$entity][$property])) {
            $value = $reflProperty->getValue($entity);
            if (is_object($value)) {
                $value = clone $value;
            }

            return 0 !== ($value <=> $this->tracked[$entity][$property]);
        }

        return false; // @codeCoverageIgnore
    }

    private function init(): void
    {
        if (isset($this->config)) {
            return;
        }

        foreach ($this->managerRegistry->getManagers() as $manager) {
            foreach ($manager->getMetadataFactory()->getAllMetadata() as $metadata) {
                $reflClass = $metadata->getReflectionClass();
                foreach ($reflClass->getProperties() as $reflProperty) {
                    foreach ($reflProperty->getAttributes(TrackChanges::class) as $reflAttribute) {
                        $this->config[$reflClass->getName()][$reflProperty->getName()] = $reflProperty;
                    }
                }
            }
        }
    }
}
