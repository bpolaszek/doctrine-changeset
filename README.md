[![CI Workflow](https://github.com/bpolaszek/doctrine-changeset/actions/workflows/ci.yml/badge.svg)](https://github.com/bpolaszek/doctrine-changeset/actions/workflows/ci.yml)
[![Coverage](https://codecov.io/gh/bpolaszek/doctrine-changeset/branch/main/graph/badge.svg?token=L5ulTaymbt)](https://codecov.io/gh/bpolaszek/doctrine-changeset)

# Doctrine Changes Tracker

This package provides an `EntityTracker` service for your Doctrine entities. 
This service allows you to easily track changes made to an entity, i.e.

```php
$entity->name = 'foo';
$tracker->hasChanged($entity); // true
$tracker->hasChanged($entity, 'name'); // true

$changeSet=$tracker->getChangeSet($entity, 'name');
$changeSet->from; // Previous value
$changeSet->to; // New value
$changeSet->hadPreviousValue('foo'); // false
$changeSet->hasNewValue('bar'); // false
$changeSet->hasChangedFor(null, 'foo'); // true
$changeSet->hadPreviousValue(whatever()->except(null)); // false
$changeSet->hasNewValue(oneOf('foo', 'bar')); // true
```

This package also provides a `#[TrackChanges]` attributes on properties typed as `object` - 
this allows Doctrine to be aware on changes on nested objects (which isn't the case by default, unless you assign a different object).

## Usage

Just inject `EntityTracker` in your services and use the helper methods.

### Example:
```php
<?php

namespace App\Services;

use App\Entity\Book;
use BenTools\DoctrineChangeSet\Tracker\EntityTracker;
use Doctrine\ORM\EntityManagerInterface;

use function assert;
use function BenTools\DoctrineChangeSet\Tracker\oneOf;
use function BenTools\DoctrineChangeSet\Tracker\whatever;

final readonly class MyService {
    public function __construct(
        private EntityTracker $tracker,
        private EntityManagerInterface $em,
    ) {}
    
    public function createBook(): void 
    {
        $book = new Book();
        $book->title = 'PHP For Dummiez';
        $book->isbn = '00000000001';
        
        assert(true === $this->tracker->hasChanged($book));
        
        $this->em->persist($book);
        $this->em->flush();
    }
    
    public function updateBook(): void 
    {
        $repository = $this->em->getRepository(Book::class)
        $book = $repository->findOneBy(['isbn' => '00000000001']);
        $book->title = 'PHP For Dummies';
        
        assert(true === $this->tracker->hasChanged($book));
        assert(true === $this->tracker->hasChanged($book, 'title'));
        assert(false === $this->tracker->hasChanged($book, 'isbn'));
        assert(true === $this->tracker->getChangeSet($book, 'title')->hadPreviousValue('PHP For Dummiez'));
        assert(true === $this->tracker->getChangeSet($book, 'title')->hasNewValue('PHP For Dummies'));
        
        // Shorthand for both methods above
        assert(true === $this->tracker->getChangeSet($book, 'title')->hasChangedFor('PHP For Dummiez', 'PHP For Dummies'));
        
        // You can also check for multiple values
        assert(true === $this->tracker->getChangeSet($book, 'title')->hadPreviousValue(oneOf('PHP For Dummies', 'PHP For Dummiez')));
        assert(true === $this->tracker->getChangeSet($book, 'title')->hasNewValue(whatever()->except(null, 'PHP For Dummiez')));
        
        $this->em->flush();
    }  
}
```

### Track object changes

By default, Doctrine doesn't track changes on embedded objects. For example:

```php
namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity]
class Book
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    public int $id;

    #[ORM\Column]
    public string $title;

    #[ORM\Column(type: Types::OBJECT)]
    public object $data;
}
```

Doing this:

```php
$book = $repository->findOneBy([])
$book->data->foo = 'bar'

$em->flush(); // Will have no effect as soon as no other scalar or array property has been changed
```

To enforce Doctrine track changes on embedded objects, simply add the `TrackChanges` attribute.
```php
namespace App\Entity;

use BenTools\DoctrineChangeSet\Tracker\TrackChanges;
// ...

#[ORM\Entity]
class Book
{
    // ...

    #[ORM\Column(type: Types::OBJECT)]
    #[TrackChanges]
    public object $data;
}
```

## Installation

```shell
composer require bentools/doctrine-changeset
```

Then, add the following line to your `bundles.php`:

```php
# config/bundles.php

return [
    // ...
    BenTools\DoctrineChangeSet\Bundle\DoctrineChangeSetBundle::class => ['all' => true],
];
```

## Tests

```shell
composer ci:check
```

## License

MIT.
