<?php

namespace ContainerUmgQ1Iw;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getHostingServiceService extends AkyosUpdates_KernelDevDebugContainer
{
    /**
     * Gets the public 'AkyosUpdates\Service\HostingService' shared autowired service.
     *
     * @return \AkyosUpdates\Service\HostingService
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/src/Service/HostingService.php';

        return $container->services['AkyosUpdates\\Service\\HostingService'] = new \AkyosUpdates\Service\HostingService();
    }
}
