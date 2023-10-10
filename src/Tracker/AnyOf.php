<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use function in_array;

/**
 * @internal
 */
final class AnyOf extends ChangeSetExpectation
{
    /**
     * @var array<int, mixed>
     */
    private array $values;

    public function __construct(mixed ...$values)
    {
        $this->values = $values;
    }

    public function match(mixed $value): bool
    {
        return in_array($value, $this->values, true);
    }
}
