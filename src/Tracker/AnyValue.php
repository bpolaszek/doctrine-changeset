<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use function in_array;

/**
 * @internal
 */
final class AnyValue extends ChangeSetExpectation
{
    /**
     * @var array<int, mixed>
     */
    private array $exceptions = [];

    public function match(mixed $value): bool
    {
        return !in_array($value, $this->exceptions, true);
    }

    public function except(mixed ...$values): self
    {
        $expectation = clone $this;
        $expectation->exceptions = $values;

        return $expectation;
    }
}
