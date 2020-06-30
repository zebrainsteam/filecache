<?php

declare(strict_types=1);

namespace Zebrains\Filecache\Contracts;

use Zebrains\Filecache\Item;

interface FormatterInterface
{
    /**
     * formats Item
     *
     * @access	public
     * @param	Item	$item
     * @return	mixed
     */
    public function getFormattedItem(Item $item);
}
