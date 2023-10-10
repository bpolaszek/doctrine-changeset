<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

function oneOf(mixed ...$values): AnyOf
{
    return new AnyOf(...$values);
}

function whatever(): AnyValue
{
    return new AnyValue();
}
