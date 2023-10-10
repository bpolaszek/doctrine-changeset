<?php

declare(strict_types=1);

namespace App\Entity;

use BenTools\DoctrineChangeSet\Tracker\TrackChanges;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Book
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    public int $id;

    #[ORM\Column]
    public string $title;

    #[ORM\Column]
    public string $isbn;

    #[ORM\Column]
    public DateTimeImmutable $createdAt;

    #[ORM\Column]
    public DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'json_document', nullable: true)]
    #[TrackChanges]
    public ?Metadata $trackedData = null;
}
