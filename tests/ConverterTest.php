<?php

namespace Zebrains\Filecache\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zebrains\Filecache\Converter;
use Zebrains\Filecache\Item;
use Zebrains\Filecache\InvalidArgumentException;

class ConverterTest extends MockeryTestCase
{
    /**
     * @var Converter $converter
     */
    protected $converter;

    public function setUp(): void
    {
        parent::setUp();

        $this->converter = new Converter();
    }

    /**
     * @test
     * @dataProvider getKeys
     */
    public function string_is_converted_to_item($key, $tags)
    {
        $item = $this->converter->fromKey($key, $tags);

        $filename = (string) $item;

        $this->assertEquals($tags, $item->getTags());

        $recoveredItem = $this->converter->fromFilename($filename);

        $this->assertEquals($tags, $recoveredItem->getTags());
    }

    /**
     * @test
     */
    public function exception_is_thown_if_key_is_not_allowed()
    {
        $key = 'this key is not allowed';

        $this->expectException(InvalidArgumentException::class);

        $this->converter->fromKey($key);
    }

    /**
     * getKeys.
     *
     * @access	public
     * @return	array
     */
    public function getKeys(): array
    {
        return [
            [
                '1:/products/product1',
                ['first', 'second']
            ],
            [
                '1:/',
                ['only_one_tag']
            ],
            [
                '19:/products/product1',
                ['first', 'second', 'third']
            ],
            [
                '2:/products/product5',
                []
            ],
            [
                '4:/products/product52',
                []
            ],
        ];
    }
}
