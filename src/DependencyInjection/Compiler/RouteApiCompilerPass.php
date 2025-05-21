<?php

namespace AkyosUpdates\DependencyInjection\Compiler;

use AkyosUpdates\Attribute\RouteApi;
use AkyosUpdates\Class\AbstractController;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ReflectionClass;

class RouteApiCompilerPass implements CompilerPassInterface
{
    /**
     * @throws ReflectionException
     */
    public function process(ContainerBuilder $container): void
    {
        // Parcourir tous les services définis dans le conteneur qui étendent de AbstractController
        $controllerDefinitions = array_filter($container->getDefinitions(), function ($definition) {
            return is_subclass_of($definition->getClass(), AbstractController::class);
        });

        foreach ($controllerDefinitions as $definition) {
            $class = $definition->getClass();

            if (!$class || !class_exists($class, false)) {
                continue;
            }

            $reflectionClass = new ReflectionClass($class);
            $methods = $reflectionClass->getMethods();

            foreach ($methods as $method) {
                $attributes = $method->getAttributes(RouteApi::class);

                foreach ($attributes as $attribute) {
                    /** @var RouteApi $routeAttr */
                    $routeAttr = $attribute->newInstance();
                    add_action('rest_api_init', function () use ($routeAttr, $container, $class, $method) {
                        $controller = $container->get($class);
                        $methodName = $method->getName();
                        register_rest_route('akyos_updates/v1', $routeAttr->endpoint, [
                            'methods' => $routeAttr->method,
                            'callback' => [$controller, $methodName],
                        ]);
                    });
                }
            }
        }
    }
}