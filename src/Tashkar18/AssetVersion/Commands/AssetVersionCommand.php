<?php namespace Tashkar18\AssetVersion\Commands;

use Tashkar18\AssetVersion\Formater as Formater;
use Tashkar18\AssetVersion\SymLinker as SymLinker;
use Illuminate\Console\Command;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem as File;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

class AssetVersionCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'assets:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new version for your assets to avoid cache';
/**
     * @var EscapeWork\Assets\SymLinker
     */
    protected $linker;
    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;
    /**
     * @var Illuminate\Cache\Repository
     */
    protected $cache;
    /**
     * @var array
     */
    protected $paths;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Config $config, File $file, SymLinker $linker, Cache $cache, $paths)
    {
        parent::__construct();
        $this->config = $config;
        $this->file = $file;
        $this->linker = $linker;
        $this->cache  = $cache;
        $this->paths  = $paths;
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $style      = $this->config->get('AssetVersion::style');
        $types      = $this->config->get('AssetVersion::paths');
        $oldVersion = $this->cache->get('asset-version.version');
        $version    = Carbon::now()->timestamp;
        $this->updateConfigVersion($version);
        $this->unlinkOldDirectories($types, $oldVersion, $style);
        $this->createDistDirectories($types, $version, $style);
    }

    public function updateConfigVersion($version)
    {
        $this->cache->forever('asset-version.version', $version);
    }

    public function unlinkOldDirectories($types, $oldVersion, $style)
    {
        if (! $oldVersion) {
            return;
        }

        foreach ($types as $type => $directories) {
            $dir = $this->paths['public'] . '/' . $directories['dist'];
            if ('directory' === $style) {
                $this->linker->unlink($dir . '/' . $oldVersion);
            } else {
                $this->unlinkFiles($dir);
            }
        }
    }
    public function createDistDirectories($types, $version, $style)
    {
        foreach ($types as $type => $directories) {
            $origin_dir = $this->paths['public'].'/'.$directories['origin'];
            $dist_dir   = $this->paths['public'].'/'.$directories['dist'];
            if ('directory' === $style) {
                $this->symLinkDirectory($version, $origin_dir, $dist_dir);
                $this->info($type . 'dist dir ('.$dist_dir.') successfully created!');
            } else {
                $this->symLinkFiles($version, $origin_dir, $dist_dir);
            }
        }
    }

    public function symLinkDirectory($version, $origin_dir, $dist_dir)
    {
        $this->linker->link($origin_dir, $dist_dir.'/'.$version);
    }

    public function symLinkFiles($version, $origin_dir, $dist_dir)
    {
        $files = $this->file->allFiles($origin_dir);
        foreach($files as $file) {
            $this->symLinkFile($file->getFileName(), $version, $origin_dir, $dist_dir);
        }
    }

    public function symLinkFile($fileName, $version, $origin_dir, $dist_dir)
    {
        $originalPath = $origin_dir ."/". $fileName;
        $formater = new Formater([
            'origin' => $origin_dir,
            'dist' => $dist_dir,
            'version' => $version,
            'path' => $originalPath
        ]);

        $symPath = $formater->style('append')->format();
        $this->linker->link($originalPath, $symPath);
    }


    public function unlinkFiles($dir)
    {
        $files = $this->file->allFiles($dir);
        foreach($files as $file) {
            $this->linker->unlink($dir."/".$file->getFileName());
        }
    }
}
