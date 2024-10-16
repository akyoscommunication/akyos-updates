<?php

namespace ContainerUmgQ1Iw;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class get_ServiceLocator_QaaoWjxService extends AkyosUpdates_KernelDevDebugContainer
{
    /**
     * Gets the private '.service_locator.QaaoWjx' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator
     */
    public static function do($container, $lazyLoad = true)
    {
        return $container->privates['.service_locator.QaaoWjx'] = new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService ??= $container->getService(...), [
            'request_stack' => ['services', 'request_stack', 'getRequestStackService', false],
            'http_kernel' => ['services', 'http_kernel', 'getHttpKernelService', false],
            'twig' => ['privates', 'twig', 'getTwigService', true],
            'parameter_bag' => ['privates', 'parameter_bag', 'getParameterBagService', true],
        ], [
            'request_stack' => '?',
            'http_kernel' => '?',
            'twig' => '?',
            'parameter_bag' => '?',
        ]);
    }
}
