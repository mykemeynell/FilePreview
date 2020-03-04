<?php

namespace mykemeynell\FilePreview\FileSystem;

use Carbon\Carbon;

/**
 * Class File.
 *
 * @package mykemeynell\FilePreview\FileSystem
 */
final class File
{
    /**
     * Path of the file.
     *
     * @var string
     */
    private $path;

    /**
     * Basic attributes of the file, including size and last action timestamps.
     *
     * @var array
     */
    private $attributes;

    /**
     * File constructor.
     *
     * @param string $path
     *
     * @throws \Exception
     */
    function __construct(string $path)
    {
        $this->path = $path;

        if(file_exists($path)) {
            $this->attributes = $this->stats();
        }
    }

    /**
     * Read the contents of a file.
     *
     * @return false|string
     */
    public function read()
    {
        return file_get_contents($this->path);
    }

    /**
     * Write new data into the file.
     *
     * @param $data
     *
     * @return false|int
     */
    public function write($data)
    {
        if($write = file_put_contents($this->path, $data)) {
            $this->attributes = $this->stats();
        }

        return $write;
    }

    /**
     * Get the file path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * The stats of a file or link.
     *
     * @return array
     */
    private function stats(): array
    {
        $stats = lstat($this->path);

        return [
            'size' => $stats['size'],
            //  Size of file in bytes
            'atime' => $stats['atime'],
            // Time of last access (unix timestamp)
            'mtime' => $stats['mtime'],
            // Time of last modification (unix timestamp)
            'ctime' => $stats['ctime'],
            // Time of last inode change (unix timestamp)
        ];
    }

    /**
     * Get the file size.
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->attributes['size'];
    }

    /**
     * Returns the filesize as a human readable string.
     *
     * @param int $places
     *
     * @return string
     */
    public function getHumanSize($places = 2): string
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($this->getSize()) - 1) / 3);

        return sprintf("%.{$places}f",
                $this->getSize() / pow(1024, $factor)) . @$size[$factor];
    }

    /**
     * Get the last access time.
     *
     * @return \Carbon\Carbon
     */
    public function getLastAccessTime(): Carbon
    {
        return Carbon::createFromTimestamp(
            $this->attributes['atime']
        );
    }

    /**
     * Get the last modification time.
     *
     * @return \Carbon\Carbon
     */
    public function getLastModificationTime(): Carbon
    {
        return Carbon::createFromTimestamp(
            $this->attributes['mtime']
        );
    }

    /**
     * Get the last modification time.
     *
     * @return \Carbon\Carbon
     */
    public function getLastInodeChangeTime(): Carbon
    {
        return Carbon::createFromTimestamp(
            $this->attributes['ctime']
        );
    }

    /**
     * Tells whether the filename is a regular file.
     *
     * @return bool
     */
    public function isFile(): bool
    {
        return is_file($this->path);
    }

    /**
     * Tells whether the filename is a symbolic link.
     *
     * @return bool
     */
    public function isLink(): bool
    {
        return is_link($this->path);
    }

    /**
     * Detect MIME content-type for a file.
     *
     * @return string
     */
    public function mime(): string
    {
        return mime_content_type($this->path);
    }

    /**
     * Test that the given file is a PDF.
     *
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->is('application/pdf');
    }

    /**
     * Test that the given file is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return substr($this->mime(), 0, strlen('image/')) === 'image/';
    }

    /**
     * Tests given MIMEs against the file MIME.
     *
     * @param $mimes
     *
     * @return bool
     */
    public function is($mimes): bool
    {
        $mimes = (array)$mimes;

        foreach ($mimes as $mime) {
            if ($this->mime() === $mime) {
                return true;
            }
        }

        return false;
    }
}
