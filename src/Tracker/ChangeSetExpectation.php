<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

/**
 * @internal
 */
abstract class ChangeSetExpectation
{
    abstract public function match(mixed $value): bool;

    public static function from(mixed $value): self
    {
        return $value instanceof self ? $value : new AnyOf($value);
    }
}
