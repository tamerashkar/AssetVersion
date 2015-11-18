<?php namespace Tashkar18\AssetVersion;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class AssetVersionServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
	public function boot()
	{
        $app   = $this->app;
		$this->package('tashkar18/AssetVersion');

		$this->setupAlias();


		$cache = $this->getCacheDriver();
        $this->app['asset'] = $this->app->share(function($app) use($cache)
        {
            return new Asset($app, $app['config'], $cache);
        });

        $this->app['asset.command'] = $this->app->share(function($app) use ($cache)
        {
            return new Commands\AssetVersionCommand($app['config'], $app['files'], new SymLinker, $cache, array(
                'app'    => app_path(),
                'public' => public_path(),
            ));
        });

        $this->commands('asset.command');


        $this->app['events']->listen('cache:cleared', function() use($app)
        {
            $app['artisan']->call('asset:version');
        });
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{

	}

	protected function setupAlias()
	{
		AliasLoader::getInstance()->alias('Asset', 'Tashkar18\AssetVersion\Facades\Asset');
	}

	protected function getCacheDriver()
	{
		return $this->app['cache']->driver();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('asset');
	}

}
