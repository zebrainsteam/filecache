<?php

namespace Zebrains\Filecache;

use Zebrains\Filecache\Contracts\InvalidatorInterface;

class ImmediateInvalidator implements InvalidatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function invalidate(string $filename): void
    {
        var_dump($filename);
    }
}
