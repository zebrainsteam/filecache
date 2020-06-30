<?php

declare(strict_types=1);

namespace Zebrains\Filecache;

class Converter
{
    /**
     * @var string $allowedSymbols
     */
    protected $allowedSymbols = '^[A-z\/_\-0-9?%:]*$';

    /**
     * fromKey.
     *
     * @access	public
     * @param	string	$key
     * @param	array 	$tags	Default: []
     * @return	Item
     */
    public function fromKey(string $key, ?array $tags = []): Item
    {
        $key = $this->getSanitized($key);

        if (! empty($tags)) {
            $sanitizedTags = [];

            foreach ($tags as $tag) {
                $sanitizedTags[] = $this->getSanitized($tag);
            }
        }

        return new Item($key, $sanitizedTags ?? []);
    }

    /**
     * fromFilename.
     *
     * @access	public
     * @param	string	$key	
     * @return	Item
     */
    public function fromFilename(string $key): Item
    {
        if (strpos($key, Item::DELIMITER) > 0) {
            return $this->fromRegex($key);
        }

        return new Item($key);
    }

    /**
     * getPreparedKey.
     *
     * @access	public
     * @param	string	$key
     * @return	string
     */
    public function getPreparedKey(string $key): string
    {
        return urlencode($key);
    }

    /**
     * getSanitized.
     *
     * @access	public
     * @param	string	$key	
     * @return	string
     */
    protected function getSanitized(string $key): string
    {
        $check = preg_match("/" . $this->allowedSymbols . "/", $key);

        if (! $check) {
            throw new InvalidArgumentException(
                'Only digits, latin letters and symbols _, -, ?, : and % are allowed in key and tags'
            );
        }

        return $this->getPreparedKey($key);
    }

    /**
     * fromRegex.
     *
     * @access	protected
     * @param	string	$key
     * @return	Item
     */
    protected function fromRegex(string $key): Item
    {
        $regex = "^(.+)\\" . Item::DELIMITER . '(.*)';

        if (! preg_match('/' . $regex . '/', $key, $matches)) {
            throw new InvalidArgumentException('Invalid key');
        }

        $key = $matches[1];
        $tags = ($matches[2] ?? '');

        if (empty($tags)) {
            return new Item($key);
        }

        $tags = explode(ITEM::TAG_DELIMITER, $tags);

        return new Item($key, $tags);
    }
}
