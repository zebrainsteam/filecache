<?php

declare(strict_types=1);

namespace Zebrains\Filecache\Formatters;

use Zebrains\Filecache\Contracts\FormatterInterface;
use Zebrains\Filecache\Item;

class RawFormatter implements FormatterInterface
{
    /**
     * formats Item
     *
     * @access	public
     * @param	Item	$item
     * @return	mixed
     */
    public function getFormattedItem(Item $item)
    {
        return $item->getData();
    }
}
