<?php namespace NetForceWS\IO;

use \Illuminate\Contracts\Foundation\Application;

class Storage implements \Illuminate\Contracts\Filesystem\Filesystem
{
    /**
     * @var string
     */
    protected $driveName = 'local';

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $drive = null;

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app       = $app;
        $this->config    = array_merge([], ['disk' => $this->driveName], $config);
        $this->driveName = $this->config['disk'];
        $this->drive     = \Storage::drive($this->driveName);
    }

    /**
     * Tratar pasta do inquilino
     * @param $path
     * @return string
     */
    protected function getPathPrefix($path)
    {
        $path_tenant = \Auth::check() ? \Auth::user()->inquilino->dominio : '__public';
        $path = \File::combine($path_tenant, $path);
        return $path;
    }

    /**
     * Determine if a file exists.
     *
     * @param  string  $path
     * @return bool
     */
    public function exists($path)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->exists($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get($path)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->get($path);
    }

    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string|resource  $contents
     * @param  string  $visibility
     * @return bool
     */
    public function put($path, $contents, $visibility = null)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->put($path, $contents, $visibility);
    }

    /**
     * Get the visibility for the given path.
     *
     * @param  string  $path
     * @return string
     */
    public function getVisibility($path)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->getVisibility($path);
    }

    /**
     * Set the visibility for the given path.
     *
     * @param  string  $path
     * @param  string  $visibility
     * @return void
     */
    public function setVisibility($path, $visibility)
    {
        $path = $this->getPathPrefix($path);
        $this->drive->setVisibility($path, $visibility);
    }

    /**
     * Prepend to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public function prepend($path, $data)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->prepend($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public function append($path, $data)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->append($path, $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param  string|array  $paths
     * @return bool
     */
    public function delete($paths)
    {
        if (is_string($paths))
        {
            $paths = [$paths];
        }

        if (is_array($paths))
        {
            for ($i = 0; $i < count($paths); $i++)
            {
                $paths[$i] = $this->getPathPrefix($paths[$i]);
            }
        }

        return $this->drive->delete($paths);
    }

    /**
     * Copy a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function copy($from, $to)
    {
        $from = $this->getPathPrefix($from);
        $to   = $this->getPathPrefix($to);
        return $this->drive->copy($from, $to);
    }

    /**
     * Move a file to a new location.
     *
     * @param  string  $from
     * @param  string  $to
     * @return bool
     */
    public function move($from, $to)
    {
        $from = $this->getPathPrefix($from);
        $to   = $this->getPathPrefix($to);
        return $this->drive->move($from, $to);
    }

    /**
     * Get the file size of a given file.
     *
     * @param  string  $path
     * @return int
     */
    public function size($path)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->size($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param  string  $path
     * @return int
     */
    public function lastModified($path)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->lastModified($path);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        $directory = $this->getPathPrefix($directory);
        return $this->drive->files($directory, $recursive);
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allFiles($directory = null)
    {
        $directory = $this->getPathPrefix($directory);
        return $this->drive->allFiles($directory);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @param  bool  $recursive
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        $directory = $this->getPathPrefix($directory);
        return $this->drive->directories($directory, $recursive);
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param  string|null  $directory
     * @return array
     */
    public function allDirectories($directory = null)
    {
        $directory = $this->getPathPrefix($directory);
        return $this->drive->allDirectories($directory);
    }

    /**
     * Create a directory.
     *
     * @param  string  $path
     * @return bool
     */
    public function makeDirectory($path)
    {
        $path = $this->getPathPrefix($path);
        return $this->drive->makeDirectory($path);
    }

    /**
     * Recursively delete a directory.
     *
     * @param  string  $directory
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        $directory = $this->getPathPrefix($directory);
        return $this->drive->deleteDirectory($directory);
    }
}