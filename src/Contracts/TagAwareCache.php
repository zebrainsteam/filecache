<?php

declare(strict_types=1);

namespace Zebrains\Filecache\Contracts;

use Psr\SimpleCache\CacheInterface;

interface TagAwareCache extends CacheInterface
{
    /**
     * setWithTags.
     *
     * @access	public
     * @param	mixed	$key  	
     * @param	mixed	$value	
     * @param	array	$tags 	Default: []
     * @param	mixed	$ttl  	Default: null
     * @return	mixed
     */
    public function setWithTags($key, $value, $tags = [], $ttl = null);

    /**
     * getByTag.
     *
     * @access	public
     * @param	string	$tag
     * @return	array|null
     */
    public function getByTag(string $tag): ?array;
}
