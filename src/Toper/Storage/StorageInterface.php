<?php
namespace Toper\Storage;

interface StorageInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return null
     */
    public function set($key, $value);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key);

    /**
     * @return null
     */
    public function save();
}
