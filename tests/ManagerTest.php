<?php

namespace Zebrains\Filecache\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zebrains\Filecache\Converter;
use Zebrains\Filecache\Item;
use Zebrains\Filecache\Manager;
use Zebrains\Filecache\ImmediateInvalidator;
use Zebrains\Filecache\Formatters\{NativeFormatter, RawFormatter};

class ManagerTest extends MockeryTestCase
{
    /**
     * @test
     */
    public function creation_search_and_removal_items_works_correct()
    {
        $path = __DIR__ . '/data';

        $ttl = strtotime('now') + 60*60*24;

        $manager = new Manager($path, new Converter(), new NativeFormatter(), new ImmediateInvalidator());

        $manager->clear();

        $this->assertEmpty(glob($path . '/*.txt'));

        $data = 'Hi, I am a cached data';

        $entries = $this->getCacheData();

        foreach ($entries as $meta) {
            if (empty($meta['tags'])) {
                $manager->set($meta['key'], $data, $ttl);
            } else {
                $manager->setWithTags($meta['key'], $data, $meta['tags']);
            }

            $this->assertTrue($manager->has($meta['key']));
            $this->assertEquals($data, $manager->get($meta['key'])->getData());
        }

        $this->assertEquals(2, count($manager->getByMask('1:', '*')));
        $this->assertEquals(2, count($manager->getByMask('2:', '*', 'section_one', '*')));

        $this->assertEquals(3, count($manager->getByTag('first')));
        $this->assertEquals(2, count($manager->getByTag('second')));
        $this->assertEmpty($manager->getByTag('non-existent-tag'));

        $this->assertEquals(count($entries), count(glob($path . '/*.txt')));

        $this->assertTrue($manager->delete($entries[0]['key']));
        $this->assertTrue($manager->delete($entries[1]['key']));

        $manager->clearByTag('first');

        $this->assertFalse($manager->has($entries[2]['key']));
        $this->assertFalse($manager->has($entries[4]['key']));

        $manager->clear();

        $this->assertEmpty(glob($path . '/*.txt'));
    }

    /**
     * @test
     */
    public function ttl_works_correct()
    {
        $path = __DIR__ . '/data';

        $manager = new Manager($path, new Converter(), new NativeFormatter(), new ImmediateInvalidator());
        
        $key = '1:/products/product1';
        $data = 'Hi, I am a cached data';
        $ttl = strtotime('now') - 10;

        $manager->set($key, $data, $ttl);

        $this->assertEmpty($manager->get($key));
    }

    /**
     * @test
     */
    public function raw_formatter_returns_correct_data()
    {
        $path = __DIR__ . '/data';

        $manager = new Manager($path, new Converter(), new RawFormatter(), new ImmediateInvalidator());

        $key = '1:/products/product1';
        $data = 'Hi, I am a cached data';

        $manager->set($key, $data);

        $this->assertEquals($data, $manager->get($key));

        $manager->clear();
    }

    /**
     * @test
     */
    public function multiple_keys_are_stored_and_deleted()
    {
        $path = __DIR__ . '/data';

        $manager = new Manager($path, new Converter(), new RawFormatter(), new ImmediateInvalidator());

        $data = [
            '1:/products/product1' => 'First cached data',
            '1:/products/product2' => 'Second cached data',
        ];

        $manager->setMultiple($data);

        $this->assertEquals($manager->get('1:/products/product1'), 'First cached data');
        $this->assertEquals($manager->get('1:/products/product2'), 'Second cached data');

        $this->assertEquals(
            array_values($data),
            $manager->getMultiple(array_keys($data))
        );

        $manager->deleteMultiple(array_keys($data));

        $this->assertFalse($manager->has('1:/products/product1'));
        $this->assertFalse($manager->has('1:/products/product2'));

        $manager->clear();
    }

    /**
     * getData.
     *
     * @access	public
     * @return	array
     */
    public function getCacheData(): array
    {
        return [
            [
                'key' => '1:/products/product1',
                'tags' => [],
            ],
            [
                'key' => '1:/products/product2',
                'tags' => ['first', 'second'],
            ],
            [
                'key' => '2:/products/product2',
                'tags' => ['first'],
            ],
            [
                'key' => '2:/catalog/section_one',
                'tags' => ['second'],
            ],
            [
                'key' => '2:/catalog/section_one/discounts',
                'tags' => ['first', 'third'],
            ],
            [
                'key' => '3:/catalog/section_one/discounts',
                'tags' => [],
            ],
        ];
    }
}
