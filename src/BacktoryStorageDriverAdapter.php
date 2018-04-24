<?php
namespace Backtory\Storage\Laravel;

use Backtory\Storage\Core\Exception\BacktoryException;
use Backtory\Storage\Core\Facade\BacktoryStorage;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;

/**
 * Class BacktoryStorageDriverAdapter
 * @package Backtory\Storage\Laravel
 */
class BacktoryStorageDriverAdapter extends AbstractAdapter
{
    /**
     * BacktoryStorageDriverAdapter constructor.
     * @param $prefix
     */
    function __construct($prefix)
    {
        $this->setPathPrefix($prefix);
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return string
     */
    public function write($path, $contents, Config $config)
    {
        $result = $this->getFileNameAndPath($path);

        $name = isset($result["name"]) ? $result["name"] : uniqid() . ".tmp";
        $path = $result["path"];

        $tmpFile = tmpfile();
        fwrite($tmpFile, $contents);

        $file = BacktoryStorage::put($tmpFile, $path);
        $this->rename($file[0], $name);

        return "{$path}/{$name}";
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return string
     */
    public function writeStream($path, $resource, Config $config)
    {
        $result = $this->getFileNameAndPath($path);
        $remotePath = in_array($result["path"], [".", ""]) ? "/" : $result["path"];
        $file = BacktoryStorage::put($resource, $remotePath);

        if (isset($result["name"])) {
            $this->rename($file[0], $result["name"]);
        }

        return $path;
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return string
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return string
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        try {
            if (strpos($newpath, ".") == 0) {
                return BacktoryStorage::move($path, $newpath);
            }
        } catch (BacktoryException $exception) {

        }

        if (is_array($path)) {
            return BacktoryStorage::renameFiles($path);
        }

        return BacktoryStorage::rename($path, $newpath);
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        return BacktoryStorage::copy($path, $newpath);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $paths = is_array($path) ? $path : [$path];
        try {
            foreach ($paths as $path) {
                BacktoryStorage::delete($path);
            }

            return true;
        } catch (BacktoryException $exception) {
            return false;
        }
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        if (substr($dirname, -1) != "/") {
            $dirname .= "/";
        }
        
        return $this->delete($dirname);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        return BacktoryStorage::createDirectory($dirname);
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        return false;
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        try {
            return BacktoryStorage::exists($path);
        } catch (BacktoryException $exception) {
            return false;
        }
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        return ["contents" => BacktoryStorage::get($path)];
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        return $this->read($path);
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool|int $page
     * @return array
     * @internal param bool $recursive
     *
     */
    public function listContents($directory = '/', $page = true)
    {
        $files = BacktoryStorage::directoryInfo($directory);
        $result = [];

        foreach ($files as $file) {
            $result[] = $this->normaliseObject($file);
        }

        return $result;
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        return BacktoryStorage::fileInfo($path);
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        $info = $this->getMetadata($path);

        if (!empty($info)) {
            return ["size" => $info["realFileSizeInBytes"]];
        }

        return ["size" => false];
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        return false;
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        $info = $this->getMetadata($path);

        if (!empty($info)) {
            return ["timestamp" => (int)($info["lastModificationDate"] / 1000)];
        }

        return ["timestamp" => false];
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        return false;
    }

    /**
     * @param $path
     * @return bool|string
     */
    public function getUrl($path)
    {
        return BacktoryStorage::url($path);
    }

    /**
     * @param $path
     * @return array
     */
    private function getFileNameAndPath($path)
    {
        $dirName = dirname($path);
        if (strpos($path, ".") > 0) {
            return [
                "name" => basename($path),
                "path" => in_array($dirName, ['.', '', '..']) ? '/' : $dirName
            ];
        } else {
            return [
                "path" => $path
            ];
        }
    }

    /**
     * Returns a dictionary of object metadata from an object.
     *
     *
     * @param $object
     * @return array
     */
    protected function normaliseObject($object)
    {
        return [
            "path" => str_replace(dirname($object->url), "", $object->url),
            "type" => $object->isDirectory ? "dir" : "file",
            'timestamp' => (int)($object->lastModificationDate / 1000),
            'size' => $object->realFileSizeInBytes
        ];
    }
}
