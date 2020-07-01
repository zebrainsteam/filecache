<?php

declare(strict_types=1);

namespace Zebrains\Filecache;

use RuntimeException;

class Storage
{
    /**
     * Creates directory if it does not exist
     *
     * @access	public
     * @param	string	$path	
     * @return	void
     */
    public function init(string $path): void
    {
        if (is_dir($path) && is_writable($path)) {
            return;
        }

        if (! mkdir($path, 0777, true)) {
            throw new RuntimeException('Unable to create directory ' . $path);
        }
    }

    /**
     * getFileList.
     *
     * @access	public
     * @param	string	$path
     * @return	array<string>
     */
    public function getFileList(string $path): array
    {
        return glob($path);
    }

    /**
     * delete.
     *
     * @access	public
     * @param	string	$filename
     * @return	void
     */
    public function delete(string $filename): void
    {
        if (is_file($filename)) {
            unlink($filename);
        }
    }

    /**
     * getContents.
     *
     * @access	public
     * @param	string	$filename
     * @return	mixed
     */
    public function getContents(string $filename)
    {
        return file_get_contents($filename);
    }

    /**
     * putContents.
     *
     * @access	public
     * @param	string	$filename
     * @param	mixed 	$data
     * @return	void
     */
    public function putContents(string $filename, $data)
    {
        if (file_put_contents($filename, $data) === false) {
            throw new RuntimeException('Unable to write data to ' . $filename);
        }
    }
}
