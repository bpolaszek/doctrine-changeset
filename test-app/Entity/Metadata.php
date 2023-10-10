<?php

declare(strict_types=1);

namespace App\Entity;

use JsonSerializable;

final class Metadata implements JsonSerializable
{
    public function __construct(
        public string $foo,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'foo' => $this->foo,
        ];
    }
}
