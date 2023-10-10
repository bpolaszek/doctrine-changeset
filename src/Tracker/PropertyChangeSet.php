<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use function func_num_args;

/**
 * @internal
 */
final readonly class PropertyChangeSet
{
    private bool $changed;

    public function __construct(
        public string $property,
        public mixed $from = null,
        public mixed $to = null,
    ) {
        $this->changed = func_num_args() > 1;
    }

    public function hadPreviousValue(mixed $expectedOldValue): bool
    {
        return $this->changed && ChangeSetExpectation::from($expectedOldValue)->match($this->from);
    }

    public function hasNewValue(mixed $expectedNewValue): bool
    {
        return $this->changed && ChangeSetExpectation::from($expectedNewValue)->match($this->to);
    }

    public function hasChangedFor(mixed $expectedOldValue, mixed $expectedNewValue): bool
    {
        return $this->hadPreviousValue($expectedOldValue) && $this->hasNewValue($expectedNewValue);
    }
}
