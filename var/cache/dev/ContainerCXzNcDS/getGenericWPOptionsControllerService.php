<?php

namespace ContainerCXzNcDS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getGenericWPOptionsControllerService extends AkyosUpdates_KernelDevDebugContainer
{
    /**
     * Gets the public 'AkyosUpdates\Controller\GenericWPOptionsController' shared autowired service.
     *
     * @return \AkyosUpdates\Controller\GenericWPOptionsController
     */
    public static function do($container, $lazyLoad = true)
    {
        include_once \dirname(__DIR__, 4).'/vendor/symfony/service-contracts/ServiceSubscriberInterface.php';
        include_once \dirname(__DIR__, 4).'/vendor/symfony/framework-bundle/Controller/AbstractController.php';
        include_once \dirname(__DIR__, 4).'/src/Controller/GenericWPOptionsController.php';

        $container->services['AkyosUpdates\\Controller\\GenericWPOptionsController'] = $instance = new \AkyosUpdates\Controller\GenericWPOptionsController();

        $instance->setContainer(($container->privates['.service_locator.QaaoWjx'] ?? $container->load('get_ServiceLocator_QaaoWjxService'))->withContext('AkyosUpdates\\Controller\\GenericWPOptionsController', $container));

        return $instance;
    }
}
