<?php namespace Tashkar18\AssetVersion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \EscapeWork\Assets\Asset
 */
class Asset extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'asset'; }

}
