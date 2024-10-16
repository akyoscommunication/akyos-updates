<?php

namespace ContainerCXzNcDS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getAdminRouteService extends AkyosUpdates_KernelDevDebugContainer
{
    /**
     * Gets the public 'AkyosUpdates\Attribute\AdminRoute' shared autowired service.
     *
     * @return \AkyosUpdates\Attribute\AdminRoute
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/src/Attribute/AdminRoute.php';

        return $container->services['AkyosUpdates\\Attribute\\AdminRoute'] = new \AkyosUpdates\Attribute\AdminRoute();
    }
}
