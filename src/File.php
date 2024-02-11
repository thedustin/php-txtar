<?php

declare(strict_types=1);

namespace Thedustin\PhpTxtar;

class File
{
    public function __construct(
        public readonly string $name,
        public readonly string $data,
    ) {
    }
}
