<?php

declare(strict_types=1);

namespace Zebrains\Filecache;

use Zebrains\Filecache\Contracts\TagAwareCache;
use Zebrains\Filecache\Contracts\MaskAwareCache;
use Zebrains\Filecache\Contracts\FormatterInterface;
use Zebrains\Filecache\Contracts\InvalidatorInterface;

class Manager implements TagAwareCache, MaskAwareCache
{
    /**
     * @var string $basePath
     */
    protected $basePath;

    /**
     * @var Converter $converter
     */
    protected $converter;

    /**
     * @var FormatterInterface $converter
     */
    protected $formatter;

    /**
     * @var InvalidatorInterface $invalidator
     */
    protected $invalidator;

    /**
     * @var Storage $storage
     */
    protected $storage;

    public function __construct(
        string $path, 
        Converter $converter, 
        FormatterInterface $formatter,
        InvalidatorInterface $invalidator,
        Storage $storage = null
    )
    {
        if (empty($storage)) {
            $storage = new Storage();
        }
        
        $this->converter = $converter;
        $this->formatter = $formatter;
        $this->invalidator = $invalidator;
        $this->storage = $storage;

        $this->storage->init($path);

        $this->basePath = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = null)
    {
        $item = $this->getOneByKey($key);

        if (empty($item)) {
            return $default;
        }

        return $this->formatter->getFormattedItem($item);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $item = $this->converter->fromKey($key);

        $item->setData($value);

        if (! empty($ttl)) {
            $item->setTtl($ttl);
        }

        $this->store($item);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key)
    {
        $search = $this->getOneByKey($key);

        if (empty($search)) {
            return false;
        }

        $filename = $this->basePath . DIRECTORY_SEPARATOR . $search . '.txt';

        unlink($filename);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $files = $this->storage->getFileList($this->basePath . DIRECTORY_SEPARATOR . '*.txt');

        foreach ($files as $filename) {
            $this->storage->delete($filename);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $results = [];

        foreach ($keys as $key) {
            $result = $this->get($key, $default);

            if (! empty($result)) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        $search = $this->getOneByKey($key);

        return (! empty($search));
    }

    /**
     * {@inheritDoc}
     */
    public function setWithTags($key, $value, $tags = [], $ttl = null)
    {
        $item = $this->converter->fromKey($key, $tags);

        $item->setData($value);

        $this->store($item);
    }

    /**
     * {@inheritDoc}
     */
    public function getByMask(string ...$masks): ?array
    {
        $mask = '';

        foreach ($masks as $element) {
            if ($element !== '*') {
                $element = $this->converter->getPreparedKey($element);
            }

            $mask .= $element;
        }

        return $this->getSearchResults($mask);
    }

    /**
     * {@inheritDoc}
     */
    public function getByTag(string $tag): ?array
    {
        return $this->getSearchResults('*', '*' . $tag . '*');
    }

    /**
     * {@inheritDoc}
     */
    public function clearByTag(string $tag): void
    {
        $entries = $this->getSearchResults('*', '*' . $tag . '*');

        if (empty($entries)) {
            return;
        }

        foreach ($entries as $entry) {
            $filename = $this->basePath . DIRECTORY_SEPARATOR . (string) $entry . '.txt';

            $this->storage->delete($filename);
        }
    }

    /**
     * getOneByKey.
     *
     * @access	protected
     * @param	string	$key
     * @return	mixed
     */
    protected function getOneByKey(string $key)
    {
        $results = $this->getByKey($key);

        if (empty($results)) {
            return null;
        }

        if (count($results) > 1) {
            throw new InvalidArgumentException('More then one results found');
        }

        return $results[0] ?? null;
    }

    /**
     * getByKey.
     * 
     * @access	protected
     * @param	string	$key	
     * @return	array|null
     */
    protected function getByKey(string $key): ?array
    {
        $key = $this->converter->getPreparedKey($key);

        return $this->getSearchResults($key);
    }

    /**
     * getSearchResults.
     *
     * @access	protected
     * @param	string	$mask
     * @param	string	$tag 	Default: '*'
     * @return	mixed
     */
    protected function getSearchResults(string $mask, string $tag = '*'): ?array
    {
        $mask = $this->basePath . DIRECTORY_SEPARATOR . $mask . Item::DELIMITER . $tag . '.txt';

        $files = $this->storage->getFileList($mask);

        if (empty($files)) {
            return null;
        }

        $items = [];

        $now = strtotime('now');

        foreach ($files as $filename) {
            $content = json_decode($this->storage->getContents($filename), true);

            if (isset($content['ttl']) && $content['ttl'] < $now) {
                $this->invalidator->invalidate($filename);

                continue;
            }

            $item = $this->converter->fromFilename(
                $this->getKeyFromFilename($filename)
            );

            $item->setContent($content);

            $items[] = $item;
        }

        return $items;
    }

    /**
     * getKeyFromFilename.
     *
     * @access	protected
     * @param	string	$filename
     * @return	string
     */
    protected function getKeyFromFilename(string $filename): string
    {
        return strtr($filename, [
            $this->basePath . DIRECTORY_SEPARATOR => '',
            '.txt' => '',
        ]);
    }

    /**
     * store.
     *
     * @access	protected
     * @param	Item	$item
     * @return	void
     */
    protected function store(Item $item): void
    {
        if ($this->has($item->getKey())) {
            throw new InvalidArgumentException('Data already exists');
        }

        $filename = $this->basePath . DIRECTORY_SEPARATOR . $item->__toString() . '.txt';

        $this->storage->putContents($filename, json_encode($item));
    }
}
