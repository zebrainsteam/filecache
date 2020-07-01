<?php

declare(strict_types=1);

namespace Zebrains\Filecache\Contracts;

use Psr\SimpleCache\CacheInterface;

interface MaskAwareCache extends CacheInterface
{
    /**
     * getByMask.
     *
     * @access	public
     * @param	string	$mask
     * @return	array|null
     */
    public function getByMask(string ...$masks): ?array;
}
