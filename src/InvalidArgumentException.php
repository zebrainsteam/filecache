<?php

declare(strict_types=1);

namespace Zebrains\Filecache;

use Psr\SimpleCache\InvalidArgumentException as InvalidArgumentExceptionInterface;
use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements InvalidArgumentExceptionInterface
{

}
