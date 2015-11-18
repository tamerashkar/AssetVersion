<?php namespace Tashkar18\AssetVersion;

use Mockery as m;

class AssetTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->app    = m::mock('Illuminate\Foundation\Application');
        $this->config = m::mock('Illuminate\Config\Repository');
        $this->cache  = m::mock('Illuminate\Cache\Repository');
    }

    public function test_version_with_local_enviroment()
    {
        $css   = 'assets/css/main.css';
        $asset = m::mock('\Tashkar18\AssetVersion\Asset[asset]', array($this->app, $this->config, $this->cache));

        $this->app->shouldReceive('environment')->once()->withNoArgs()->andReturn('local');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::environments')->andReturn(array('production'));
        $asset->shouldReceive('asset')->once()->with($css)->andReturn('/' . $css);

        $this->assertEquals('/' . $css, $asset->version($css));
    }

    public function test_version_with_production_environment($value='')
    {
        $css   = 'assets/css/main.css';
        $asset = m::mock('\Tashkar18\AssetVersion\Asset[updateVersion,asset]', array($this->app, $this->config, $this->cache));

        $this->app->shouldReceive('environment')->once()->withNoArgs()->andReturn('production');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::environments')->andReturn(array('production'));
        $asset->shouldReceive('updateVersion')->once()->with($css)->andReturn('assets/dist/css/12345/main.css');
        $asset->shouldReceive('asset')->once()->with('assets/dist/css/12345/main.css')->andReturn('/assets/dist/css/12345/main.css');

        $this->assertEquals('/assets/dist/css/12345/main.css', $asset->version($css));
    }


    public function test_update_version_with_existing_extension_and_directory_style()
    {
        $dirs = array('origin' => 'assets/stylesheets/css', 'dist' => 'assets/stylesheets/dist');
        $this->cache->shouldReceive('get')->once()->with('asset-version.version')->andReturn('0.0.1');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::paths.css')->andReturn($dirs);
        $this->config->shouldReceive('get')->once()->with('AssetVersion::style')->andReturn('directory');

        $asset = new Asset($this->app, $this->config, $this->cache);
        $this->assertEquals('assets/stylesheets/dist/0.0.1/main.css', $asset->updateVersion('assets/stylesheets/css/main.css'));
    }

    public function test_update_version_with_existing_extension_and_append_style()
    {
        $dirs = array('origin' => 'assets/stylesheets/css', 'dist' => 'assets/stylesheets/dist');
        $this->cache->shouldReceive('get')->once()->with('asset-version.version')->andReturn('0.0.1');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::paths.css')->andReturn($dirs);
        $this->config->shouldReceive('get')->once()->with('AssetVersion::style')->andReturn('append');

        $asset = new Asset($this->app, $this->config, $this->cache);
        $this->assertEquals('assets/stylesheets/dist/main-0.0.1.css', $asset->updateVersion('assets/stylesheets/css/main.css'));
    }

    public function test_update_version_with_non_existing_extension()
    {
        $this->cache->shouldReceive('get')->once()->with('asset-version.version')->andReturn('0.0.1');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::paths.css')->andReturn(null);

        $asset = new Asset($this->app, $this->config, $this->cache);
        $this->assertEquals('assets/stylesheets/css/main.css', $asset->updateVersion('assets/stylesheets/css/main.css'));
    }

    public function test_replace_version_with_non_valid_origin_dir()
    {
        $this->cache->shouldReceive('get')->once()->with('asset-version.version')->andReturn('0.0.1');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::paths.css')->andReturn(array(
            'origin' => 'assets/stylesheets/css',
            'dist'   => 'assets/stylesheets/dist',
        ));

        $asset = new Asset($this->app, $this->config, $this->cache);
        $this->assertEquals(
            'stylesheets/css/main.css',
            $asset->updateVersion('stylesheets/css/main.css')
        );
    }

    public function test_path_method_in_local_environment()
    {
        $this->app->shouldReceive('environment')->andReturn('local');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::paths.html')->andReturn(array(
            'origin' => 'templates'
        ));

        $asset = m::mock('Tashkar18\AssetVersion\Asset[asset]', array($this->app, $this->config, $this->cache));
        $asset->shouldReceive('asset')->once()->with('templates')->andReturn('templates');

        $this->assertEquals(
            $asset->path('html'),
            'templates'
        );
    }

    public function test_path_method_in_production_environment()
    {
        $this->app->shouldReceive('environment')->andReturn('production');
        $this->cache->shouldReceive('get')->once()->with('asset-version.version')->andReturn('12345');
        $this->config->shouldReceive('get')->once()->with('AssetVersion::paths.html')->andReturn(array(
            'dist' => 'templates/dist'
        ));

        $asset = m::mock('Tashkar18\AssetVersion\Asset[asset]', array($this->app, $this->config, $this->cache));
        $asset->shouldReceive('asset')->once()->with('templates/dist')->andReturn('templates/dist');

        $this->assertEquals(
            $asset->path('html'),
            'templates/dist/12345'
        );
    }

    public function tearDown()
    {
        m::close();
    }
}
