<?php

namespace ContainerCXzNcDS;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class getGenericWPOptionsControllergenericWPOptionsService extends AkyosUpdates_KernelDevDebugContainer
{
    /**
     * Gets the private '.service_locator.BSUJTBe.AkyosUpdates\Controller\GenericWPOptionsController::genericWPOptions()' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->privates['.service_locator.BSUJTBe.AkyosUpdates\\Controller\\GenericWPOptionsController::genericWPOptions()'] = (new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService ??= $container->getService(...), [
            'generalConfigsService' => ['services', 'AkyosUpdates\\Service\\GenericWPOptionsService', 'getGenericWPOptionsServiceService', true],
        ], [
            'generalConfigsService' => 'AkyosUpdates\\Service\\GenericWPOptionsService',
        ]))->withContext('AkyosUpdates\\Controller\\GenericWPOptionsController::genericWPOptions()', $container);
    }
}
