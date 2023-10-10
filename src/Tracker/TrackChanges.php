<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tracker;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class TrackChanges
{
}
