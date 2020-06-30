<?php

namespace Zebrains\Filecache;

use Zebrains\Filecache\Contracts\InvalidatorInterface;

class ImmediateInvalidator
{
    /**
     * {@inheritDoc}
     */
    public function invalidate(string $filename): void
    {

    }
}
