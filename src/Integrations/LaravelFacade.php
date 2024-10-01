<?php
namespace Cardei\LinkPreview\Integrations;

use Illuminate\Support\Facades\Facade;

/**
 * Class LaravelFacade
 * @package Cardei\LinkPreview\Integrations
 * @codeCoverageIgnore
 */
class LaravelFacade extends Facade
{
    /**
     * Name of the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'link-preview';
    }
}
