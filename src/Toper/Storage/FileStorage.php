<?php
namespace Toper\Storage;

class FileStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $storageDirectory;

    /**
     * @param string $storageDirectory
     */
    public function __construct($storageDirectory)
    {
        $this->storageDirectory = $storageDirectory;
    }

    /**
     * @var array|null
     */
    private $data = null;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return null
     */
    public function set($key, $value)
    {
        $this->initialize();

        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->initialize();

        if (!$this->exists($key)) {
            return null;
        }

        return $this->data[$key];
    }

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key)
    {
        $this->initialize();

        return isset($this->data[$key]);
    }

    /**
     * @return null
     */
    public function save()
    {
        if (!$this->storageDirectoryExists()) {
            $this->createStorageDirectory();
        }

        file_put_contents(
            $this->getDataFilePath(),
            serialize($this->data)
        );
    }

    /**
     * @return null
     */
    private function initialize()
    {
        if ($this->data === null) {
            if (file_exists($this->getDataFilePath())) {
                $this->data = unserialize(file_get_contents($this->getDataFilePath()));
            } else {
                $this->data = array();
            }
        }
    }

    /**
     * @return boolean
     */
    private function storageDirectoryExists()
    {
        return file_exists($this->storageDirectory);
    }

    /**
     * @return null
     */
    private function createStorageDirectory()
    {
        mkdir($this->storageDirectory, 0777, true);
    }

    /**
     * @return string
     */
    private function getDataFilePath()
    {
        return $this->storageDirectory . PATH_SEPARATOR . "toper_data";
    }
} 