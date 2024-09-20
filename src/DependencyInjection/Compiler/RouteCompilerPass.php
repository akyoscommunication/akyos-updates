<?php

namespace AkyosUpdates\DependencyInjection\Compiler;

use AkyosUpdates\Attribute\Route;
use AkyosUpdates\Class\AbstractController;
use ReflectionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ReflectionClass;

class RouteCompilerPass implements CompilerPassInterface
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

            foreach($methods as $method) {
                $attributes = $method->getAttributes(Route::class);

                foreach ($attributes as $attribute) {
                    /** @var Route $routeAttr */
                    $routeAttr = $attribute->newInstance();

                    add_action( 'admin_menu', function() use ($routeAttr, $container, $class, $method) {
                        $controller = $container->get($class);

                        if($routeAttr->type === Route::TYPE_MENU_PAGE) {
                            add_menu_page($routeAttr->pageTitle, $routeAttr->menuTitle, $routeAttr->capability, $routeAttr->slug, [$controller, $method->getName()], $routeAttr->iconUrl, $routeAttr->position);
                        } else {
                            add_submenu_page($routeAttr->parentSlug, $routeAttr->pageTitle, $routeAttr->menuTitle, $routeAttr->capability, $routeAttr->slug, [$controller, $method->getName()], $routeAttr->position);
                        }
                    });
                }
            }
        }
    }
}