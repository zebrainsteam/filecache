<?php

declare(strict_types=1);

namespace Zebrains\Filecache;

use JsonSerializable;
use Datetime;

class Item implements JsonSerializable
{
    public const DELIMITER = '#';

    public const TAG_DELIMITER = '|';

    /**
     * @var string $key
     */
    protected $key;

    /**
     * @var array<string> $tags
     */
    protected $tags;

    /**
     * @var string $data
     */
    protected $data;

    /**
     * @var int $ttl
     */
    protected $ttl;

    public function __construct(string $key, array $tags = [])
    {
        $this->key = $key;
        $this->tags = $tags;
    }

    /**
     * Get $key
     *
     * @return  string
     */ 
    public function getKey(): string
    {
        $key = $this->key;
        if (strpos($key, static::DELIMITER) === false) {
            $key = $key . static::DELIMITER;
        }

        return $key;
    }

    /**
     * Get $tags
     *
     * @return  array<string>
     */ 
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Get $data
     *
     * @return  string
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set $data
     *
     * @param  string  $data  $data
     *
     * @return  self
     */ 
    public function setData(string $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * setContent.
     *
     * @access	public
     * @param	array	$content
     * @return	self
     */
    public function setContent(array $content)
    {
        if (! empty($content['ttl'])) {
            $this->setTtl((int) $content['ttl']);
        }

        // todo: check data exists
        $this->setData($content['data']);

        return $this;
    }

    /**
     * Set $ttl
     *
     * @param  int|Datetime  $ttl  $ttl
     *
     * @return  self
     */ 
    public function setTtl($ttl)
    {
        if ($ttl instanceof Datetime) {
            // Протестировать
            $ttl = $ttl->getTimestamp();
        }

        $this->ttl = $ttl;

        return $this;
    }

    public function toArray(): array
    {
        $content = ['data' => $this->data];

        if (! empty($this->ttl)) {
            $content['ttl'] = $this->ttl;
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convertes current object into string
     *
     * @access	public
     * @return	string
     */
    public function __toString(): string
    {
        if (empty($this->tags)) {
            return $this->key . static::DELIMITER ;
        }

        return $this->key . static::DELIMITER . implode(static::TAG_DELIMITER, $this->tags);
    }
}
