<?php

use AkyosUpdates\DependencyInjection\Compiler\HookCompilerPass;
use AkyosUpdates\DependencyInjection\Compiler\RouteCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

// Crée le conteneur de services
$container = new ContainerBuilder();

$loader = new YamlFileLoader($container, new FileLocator(__DIR__));

// load services from the yaml file
$loader->load('config/services.yaml');

// load Compiler Pass for attributes
$container->addCompilerPass(new RouteCompilerPass());
$container->addCompilerPass(new HookCompilerPass());

$container->compile();

return $container;
