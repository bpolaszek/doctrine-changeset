<?php

declare(strict_types=1);

namespace BenTools\DoctrineChangeSet\Tests;

use BenTools\DoctrineChangeSet\Tracker\EntityTracker;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use SampleApp\Entity\Book;
use SampleApp\Entity\Metadata;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use function assert;
use function BenTools\DoctrineChangeSet\Tracker\oneOf;
use function BenTools\DoctrineChangeSet\Tracker\whatever;

uses(KernelTestCase::class);

test('an entity scheduled for insertion is reported as changed', function () {
    $container = $this->getContainer(); // @phpstan-ignore-line
    /** @var EntityTracker $entityChangeSet */
    $entityChangeSet = $container->get(EntityTracker::class);
    $em = $container->get(EntityManagerInterface::class);

    // Given
    $book = new Book();
    $book->title = 'PHP For Dummiez';
    $book->isbn = '0000000000001';
    $book->createdAt = new DateTimeImmutable();
    $book->updatedAt = new DateTimeImmutable();
    $book->trackedData = new Metadata('foo');

    // When
    $em->persist($book);

    // Then
    expect($entityChangeSet->hasChanged($book))->toBeTrue()
        ->and($entityChangeSet->isAboutToBeInserted($book))->toBeTrue()
        ->and($entityChangeSet->isAboutToBeUpdated($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeRemoved($book))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'title'))->toBeTrue()
        ->and($entityChangeSet->hasChanged($book, 'isbn'))->toBeTrue()
        ->and($entityChangeSet->hasChanged($book, 'createdAt'))->toBeTrue()
        ->and($entityChangeSet->hasChanged($book, 'updatedAt'))->toBeTrue()
        ->and($entityChangeSet->hasChanged($book, 'trackedData'))->toBeTrue();

    // When
    $em->flush();

    // Then
    expect($entityChangeSet->hasChanged($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeInserted($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeUpdated($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeRemoved($book))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'title'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'isbn'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'createdAt'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'updatedAt'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'trackedData'))->toBeFalse();
});
test('an unchanged entity is not reported as changed', function () {
    $container = $this->getContainer(); // @phpstan-ignore-line
    /** @var EntityTracker $entityChangeSet */
    $entityChangeSet = $container->get(EntityTracker::class);

    /** @var EntityManagerInterface $em */
    $em = $container->get(EntityManagerInterface::class);

    // Given
    $book = $em->getRepository(Book::class)->findOneBy([]);
    assert($book instanceof Book);

    // Then
    expect($entityChangeSet->hasChanged($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeInserted($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeUpdated($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeRemoved($book))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'title'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'isbn'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'createdAt'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'updatedAt'))->toBeFalse();
});
test('a changed entity is reported as changed', function () {
    $container = $this->getContainer(); // @phpstan-ignore-line
    /** @var EntityTracker $entityChangeSet */
    $entityChangeSet = $container->get(EntityTracker::class);

    /** @var EntityManagerInterface $em */
    $em = $container->get(EntityManagerInterface::class);

    // Given
    $book = $em->getRepository(Book::class)->findOneBy([]);
    assert($book instanceof Book);
    $book->isbn = '0000000000002';
    $book->updatedAt = new DateTimeImmutable();

    // Then
    expect($entityChangeSet->hasChanged($book))->toBeTrue()
        ->and($entityChangeSet->isAboutToBeInserted($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeUpdated($book))->toBeTrue()
        ->and($entityChangeSet->isAboutToBeRemoved($book))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'title'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'isbn'))->toBeTrue()
        ->and($entityChangeSet->hasChanged($book, 'createdAt'))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'updatedAt'))->toBeTrue()
        ->and(
            $entityChangeSet->getChangeSet($book, 'isbn')
                ->hasChangedFor('0000000000001', '0000000000002')
        )
        ->toBeTrue()
        ->and(
            $entityChangeSet->getChangeSet($book, 'isbn')->hasChangedFor(
                '0000000000001',
                oneOf('0000000000002', '0000000000003')
            )
        )
        ->toBeTrue()
        ->and(
            $entityChangeSet->getChangeSet($book, 'isbn')->hasChangedFor(
                whatever()->except('0000000000002'),
                oneOf('0000000000002', '0000000000003')
            )
        )
        ->toBeTrue()
        ->and(
            $entityChangeSet->getChangeSet($book, 'isbn')->hasChangedFor(
                whatever()->except('0000000000001'),
                whatever()
            )
        )
        ->toBeFalse()
        ->and(
            $entityChangeSet->getChangeSet($book, 'isbn')->hasChangedFor(
                whatever(),
                oneOf('0000000000001', '0000000000003')
            )
        )
        ->toBeFalse();
});

test('a tracked property is reported as changed', function () {
    $container = $this->getContainer(); // @phpstan-ignore-line
    /** @var EntityTracker $entityChangeSet */
    $entityChangeSet = $container->get(EntityTracker::class);

    /** @var EntityManagerInterface $em */
    $em = $container->get(EntityManagerInterface::class);

    // Given
    $book = $em->getRepository(Book::class)->findOneBy([]);
    assert($book instanceof Book);

    // When
    $book->title = 'PHP For Dummies';

    // Then
    expect($entityChangeSet->hasChanged($book))->toBeTrue()
        ->and($entityChangeSet->isAboutToBeInserted($book))->toBeFalse()
        ->and($entityChangeSet->isAboutToBeUpdated($book))->toBeTrue()
        ->and($entityChangeSet->isAboutToBeRemoved($book))->toBeFalse()
        ->and($entityChangeSet->hasChanged($book, 'title'))->toBeTrue()
        ->and($entityChangeSet->hasChanged($book, 'trackedData'))->toBeFalse();

    // When
    $book->trackedData->foo = 'bar';

    // Then
    expect($entityChangeSet->hasChanged($book, 'trackedData'))->toBeTrue();

    // When
    $em->flush();
    $em->clear();
    $book = $em->getRepository(Book::class)->findOneBy([]);

    // Then
    expect($book->trackedData->foo)->toEqual('bar');
});

it('reports an entity as scheduled for removal', function () {
    $container = $this->getContainer(); // @phpstan-ignore-line
    /** @var EntityTracker $entityChangeSet */
    $entityChangeSet = $container->get(EntityTracker::class);

    /** @var EntityManagerInterface $em */
    $em = $container->get(EntityManagerInterface::class);

    // Given
    $book = $em->getRepository(Book::class)->findOneBy([]);
    assert($book instanceof Book);

    $em->remove($book);
    expect($entityChangeSet->isAboutToBeRemoved($book))->toBeTrue();
});
