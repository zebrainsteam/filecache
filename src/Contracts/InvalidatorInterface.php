<?php

declare(strict_types=1);

namespace Zebrains\Filecache\Contracts;

interface InvalidatorInterface
{
    /**
     * invalidates Item
     *
     * @access	public
     * @param	string	$filename
     * @return	void
     */
    public function invalidate(string $filename): void;
}
