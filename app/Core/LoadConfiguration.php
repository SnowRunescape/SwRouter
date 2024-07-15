<?php

namespace App\Core;

use Exception;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Contracts\Config\Repository as RepositoryContract;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class LoadConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Container\Container  $app
     * @return void
     */
    public function bootstrap(Container $app)
    {
        $items = [];

        // Next we will spin through all of the configuration files in the configuration
        // directory and load each one into the repository. This will make all of the
        // options available to the developer for use in various parts of this app.
        $app->instance('config', $config = new Repository($items));

        if (! isset($loadedFromCache)) {
            $this->loadConfigurationFiles($app, $config);
        }

        date_default_timezone_set($config->get('app.timezone', 'UTC'));

        mb_internal_encoding('UTF-8');
    }

    /**
     * Load the configuration items from all of the files.
     *
     * @param  \Illuminate\Container\Container  $app
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @return void
     *
     * @throws \Exception
     */
    protected function loadConfigurationFiles(Container $app, RepositoryContract $repository)
    {
        $files = $this->getConfigurationFiles($app);

        $base = $this->getBaseConfiguration();

        foreach ($files as $name => $path) {
            $base = $this->loadConfigurationFile($repository, $name, $path, $base);
        }

        foreach ($base as $name => $config) {
            $repository->set($name, $config);
        }
    }

    /**
     * Load the given configuration file.
     *
     * @param  \Illuminate\Contracts\Config\Repository  $repository
     * @param  string  $name
     * @param  string  $path
     * @param  array  $base
     * @return array
     */
    protected function loadConfigurationFile(RepositoryContract $repository, $name, $path, array $base)
    {
        $config = require $path;

        if (isset($base[$name])) {
            $config = array_merge($base[$name], $config);

            foreach ($this->mergeableOptions($name) as $option) {
                if (isset($config[$option])) {
                    $config[$option] = array_merge($base[$name][$option], $config[$option]);
                }
            }

            unset($base[$name]);
        }

        $repository->set($name, $config);

        return $base;
    }

    /**
     * Get the options within the configuration file that should be merged again.
     *
     * @param  string  $name
     * @return array
     */
    protected function mergeableOptions($name)
    {
        return [
            'auth' => ['guards', 'providers', 'passwords'],
            'broadcasting' => ['connections'],
            'cache' => ['stores'],
            'database' => ['connections'],
            'filesystems' => ['disks'],
            'logging' => ['channels'],
            'mail' => ['mailers'],
            'queue' => ['connections'],
        ][$name] ?? [];
    }

    /**
     * Get all of the configuration files for the application.
     *
     * @param  \Illuminate\Container\Container  $app
     * @return array
     */
    protected function getConfigurationFiles(Container $app)
    {
        $files = [];

        $configPath = realpath(ROOT_PATH . "/config");

        if (! $configPath) {
            return [];
        }

        foreach (Finder::create()->files()->name('*.php')->in($configPath) as $file) {
            $directory = $this->getNestedDirectory($file, $configPath);

            $files[$directory.basename($file->getRealPath(), '.php')] = $file->getRealPath();
        }

        ksort($files, SORT_NATURAL);

        return $files;
    }

    /**
     * Get the configuration file nesting path.
     *
     * @param  \SplFileInfo  $file
     * @param  string  $configPath
     * @return string
     */
    protected function getNestedDirectory(SplFileInfo $file, $configPath)
    {
        $directory = $file->getPath();

        if ($nested = trim(str_replace($configPath, '', $directory), DIRECTORY_SEPARATOR)) {
            $nested = str_replace(DIRECTORY_SEPARATOR, '.', $nested).'.';
        }

        return $nested;
    }

    /**
     * Get the base configuration files.
     *
     * @return array
     */
    protected function getBaseConfiguration()
    {
        $config = [];

        foreach (Finder::create()->files()->name('*.php')->in(ROOT_PATH.'/config') as $file) {
            $config[basename($file->getRealPath(), '.php')] = require $file->getRealPath();
        }

        return $config;
    }
}
