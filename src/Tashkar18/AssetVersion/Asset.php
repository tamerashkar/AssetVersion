<?php namespace Tashkar18\AssetVersion;

use Illuminate\Foundation\Application as App;
use Illuminate\Config\Repository as Config;
use Illuminate\Cache\Repository as Cache;

class Asset
{

    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var Illuminate\Cache\Repository
     */
    protected $cache;

    protected static $cacheName = 'asset-version.version';

    public function __construct(App $app, Config $config, Cache $cache)
    {
        $this->app    = $app;
        $this->config = $config;
        $this->cache  = $cache;
    }

    public function version($path)
    {
        if (! in_array($this->app->environment(), $this->config->get('AssetVersion::environments'))) {
            return $this->asset($path);
        }

        return $this->asset($this->updateVersion($path));
    }

    public function path($extension)
    {
        $type    = $this->config->get('AssetVersion::paths.' . $extension);

        if ($this->app->environment() == 'local') {
            return $this->asset($type['origin']);
        }

        return $this->asset($type['dist']) . '/' . $this->cache->get(static::$cacheName);
    }

    public function asset($path)
    {
        return asset($path);
    }

    public function updateVersion($path)
    {
        $version   = $this->cache->get(static::$cacheName);
        $file      = explode('.', $path);
        $extension = $file[count($file) - 1];
        $type      = $this->config->get('AssetVersion::paths.' . $extension);

        if ( ! $type) {
            return $path;
        }

        if ( ! preg_match("#^\/?" . $type['origin'] . "#", $path)) {
            return $path;
        }

        $formater = new Formater([
            'origin' => $type['origin'],
            'dist' => $type['dist'],
            'version' => $version,
            'path' => $path
        ]);

        return $formater->style($this->config->get('AssetVersion::style'))->format();
    }
}