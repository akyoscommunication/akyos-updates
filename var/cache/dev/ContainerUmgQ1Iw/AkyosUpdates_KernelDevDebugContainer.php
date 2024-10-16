<?php

namespace ContainerUmgQ1Iw;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @internal This class has been auto-generated by the Symfony Dependency Injection Component.
 */
class AkyosUpdates_KernelDevDebugContainer extends Container
{
    protected $targetDir;
    protected $parameters = [];
    protected \Closure $getService;

    public function __construct(private array $buildParameters = [], protected string $containerDir = __DIR__)
    {
        $this->targetDir = \dirname($containerDir);
        $this->parameters = $this->getDefaultParameters();

        $this->services = $this->privates = [];
        $this->syntheticIds = [
            'kernel' => true,
        ];
        $this->methodMap = [
            'event_dispatcher' => 'getEventDispatcherService',
            'http_kernel' => 'getHttpKernelService',
            'request_stack' => 'getRequestStackService',
        ];
        $this->fileMap = [
            'AkyosUpdates\\AkyosUpdates' => 'getAkyosUpdatesService',
            'AkyosUpdates\\Attribute\\AdminRoute' => 'getAdminRouteService',
            'AkyosUpdates\\Attribute\\Hook' => 'getHookService',
            'AkyosUpdates\\Controller\\AkyosUpdatesController' => 'getAkyosUpdatesControllerService',
            'AkyosUpdates\\Controller\\GenericWPOptionsController' => 'getGenericWPOptionsControllerService',
            'AkyosUpdates\\Controller\\HostingController' => 'getHostingControllerService',
            'AkyosUpdates\\Service\\GenericWPOptionsService' => 'getGenericWPOptionsServiceService',
            'AkyosUpdates\\Service\\HostingService' => 'getHostingServiceService',
            'AkyosUpdates\\Twig\\PluginExtension' => 'getPluginExtensionService',
            'cache.app' => 'getCache_AppService',
            'cache.app_clearer' => 'getCache_AppClearerService',
            'cache.global_clearer' => 'getCache_GlobalClearerService',
            'cache.system' => 'getCache_SystemService',
            'cache.system_clearer' => 'getCache_SystemClearerService',
            'cache_warmer' => 'getCacheWarmerService',
            'container.env_var_processors_locator' => 'getContainer_EnvVarProcessorsLocatorService',
            'container.get_routing_condition_service' => 'getContainer_GetRoutingConditionServiceService',
            'debug.error_handler_configurator' => 'getDebug_ErrorHandlerConfiguratorService',
            'error_controller' => 'getErrorControllerService',
            'services_resetter' => 'getServicesResetterService',
        ];
        $this->aliases = [
            'AkyosUpdates\\Kernel' => 'kernel',
        ];

        $this->privates['service_container'] = static function ($container) {
            include_once \dirname(__DIR__, 4).'/vendor/symfony/event-dispatcher/EventSubscriberInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/ResponseListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/LocaleListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/ValidateRequestListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/DisallowRobotsIndexingListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/ErrorListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/CacheAttributeListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/psr/event-dispatcher/src/EventDispatcherInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/event-dispatcher-contracts/EventDispatcherInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/event-dispatcher/EventDispatcherInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/event-dispatcher/EventDispatcher.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/HttpKernelInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/TerminableInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/HttpKernel.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ControllerResolverInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ControllerResolver.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ContainerControllerResolver.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/framework-bundle/Controller/ControllerResolver.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ArgumentResolverInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ArgumentResolver.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/ControllerMetadata/ArgumentMetadataFactoryInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/ControllerMetadata/ArgumentMetadataFactory.php';
            include_once \dirname(__DIR__, 4).'/vendor/psr/container/src/ContainerInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/service-contracts/ServiceProviderInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/service-contracts/ServiceCollectionInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/service-contracts/ServiceLocatorTrait.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/dependency-injection/ServiceLocator.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-foundation/RequestStack.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/EventListener/DebugHandlersListener.php';
            include_once \dirname(__DIR__, 4).'/vendor/psr/log/src/LoggerInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/psr/log/src/LoggerTrait.php';
            include_once \dirname(__DIR__, 4).'/vendor/psr/log/src/AbstractLogger.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Log/DebugLoggerInterface.php';
            include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Log/Logger.php';
        };
    }

    public function compile(): void
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled(): bool
    {
        return true;
    }

    public function getRemovedIds(): array
    {
        return require $this->containerDir.\DIRECTORY_SEPARATOR.'removed-ids.php';
    }

    protected function load($file, $lazyLoad = true): mixed
    {
        if (class_exists($class = __NAMESPACE__.'\\'.$file, false)) {
            return $class::do($this, $lazyLoad);
        }

        if ('.' === $file[-4]) {
            $class = substr($class, 0, -4);
        } else {
            $file .= '.php';
        }

        $service = require $this->containerDir.\DIRECTORY_SEPARATOR.$file;

        return class_exists($class, false) ? $class::do($this, $lazyLoad) : $service;
    }

    protected function createProxy($class, \Closure $factory)
    {
        class_exists($class, false) || require __DIR__.'/'.$class.'.php';

        return $factory();
    }

    /**
     * Gets the public 'event_dispatcher' shared service.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected static function getEventDispatcherService($container)
    {
        $container->services['event_dispatcher'] = $instance = new \Symfony\Component\EventDispatcher\EventDispatcher();

        $instance->addListener('kernel.response', [#[\Closure(name: 'response_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener')] fn () => ($container->privates['response_listener'] ??= new \Symfony\Component\HttpKernel\EventListener\ResponseListener('UTF-8', false)), 'onKernelResponse'], 0);
        $instance->addListener('kernel.request', [#[\Closure(name: 'locale_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener')] fn () => ($container->privates['locale_listener'] ?? self::getLocaleListenerService($container)), 'setDefaultLocale'], 100);
        $instance->addListener('kernel.request', [#[\Closure(name: 'locale_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener')] fn () => ($container->privates['locale_listener'] ?? self::getLocaleListenerService($container)), 'onKernelRequest'], 16);
        $instance->addListener('kernel.finish_request', [#[\Closure(name: 'locale_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\LocaleListener')] fn () => ($container->privates['locale_listener'] ?? self::getLocaleListenerService($container)), 'onKernelFinishRequest'], 0);
        $instance->addListener('kernel.request', [#[\Closure(name: 'validate_request_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\ValidateRequestListener')] fn () => ($container->privates['validate_request_listener'] ??= new \Symfony\Component\HttpKernel\EventListener\ValidateRequestListener()), 'onKernelRequest'], 256);
        $instance->addListener('kernel.response', [#[\Closure(name: 'disallow_search_engine_index_response_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\DisallowRobotsIndexingListener')] fn () => ($container->privates['disallow_search_engine_index_response_listener'] ??= new \Symfony\Component\HttpKernel\EventListener\DisallowRobotsIndexingListener()), 'onResponse'], -255);
        $instance->addListener('kernel.controller_arguments', [#[\Closure(name: 'exception_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener')] fn () => ($container->privates['exception_listener'] ?? self::getExceptionListenerService($container)), 'onControllerArguments'], 0);
        $instance->addListener('kernel.exception', [#[\Closure(name: 'exception_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener')] fn () => ($container->privates['exception_listener'] ?? self::getExceptionListenerService($container)), 'logKernelException'], 0);
        $instance->addListener('kernel.exception', [#[\Closure(name: 'exception_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener')] fn () => ($container->privates['exception_listener'] ?? self::getExceptionListenerService($container)), 'onKernelException'], -128);
        $instance->addListener('kernel.response', [#[\Closure(name: 'exception_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\ErrorListener')] fn () => ($container->privates['exception_listener'] ?? self::getExceptionListenerService($container)), 'removeCspHeader'], -128);
        $instance->addListener('kernel.controller_arguments', [#[\Closure(name: 'controller.cache_attribute_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\CacheAttributeListener')] fn () => ($container->privates['controller.cache_attribute_listener'] ??= new \Symfony\Component\HttpKernel\EventListener\CacheAttributeListener()), 'onKernelControllerArguments'], 10);
        $instance->addListener('kernel.response', [#[\Closure(name: 'controller.cache_attribute_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\CacheAttributeListener')] fn () => ($container->privates['controller.cache_attribute_listener'] ??= new \Symfony\Component\HttpKernel\EventListener\CacheAttributeListener()), 'onKernelResponse'], -10);
        $instance->addListener('kernel.request', [#[\Closure(name: 'debug.debug_handlers_listener', class: 'Symfony\\Component\\HttpKernel\\EventListener\\DebugHandlersListener')] fn () => ($container->privates['debug.debug_handlers_listener'] ??= new \Symfony\Component\HttpKernel\EventListener\DebugHandlersListener(NULL, $container->getEnv('bool:default::key:web:default:kernel.runtime_mode:'))), 'configure'], 2048);
        $instance->addListener('kernel.view', [#[\Closure(name: 'controller.template_attribute_listener', class: 'Symfony\\Bridge\\Twig\\EventListener\\TemplateAttributeListener')] fn () => ($container->privates['controller.template_attribute_listener'] ?? $container->load('getController_TemplateAttributeListenerService')), 'onKernelView'], -128);

        return $instance;
    }

    /**
     * Gets the public 'http_kernel' shared service.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernel
     */
    protected static function getHttpKernelService($container)
    {
        $a = ($container->services['event_dispatcher'] ?? self::getEventDispatcherService($container));

        if (isset($container->services['http_kernel'])) {
            return $container->services['http_kernel'];
        }
        $b = new \Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver($container, ($container->privates['logger'] ?? self::getLoggerService($container)));
        $b->allowControllers(['Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController', 'Symfony\\Bundle\\FrameworkBundle\\Controller\\TemplateController']);
        $b->allowControllers(['AkyosUpdates\\Kernel', 'AkyosUpdates\\Controller\\AkyosUpdatesController', 'AkyosUpdates\\Controller\\GenericWPOptionsController', 'AkyosUpdates\\Controller\\HostingController']);

        return $container->services['http_kernel'] = new \Symfony\Component\HttpKernel\HttpKernel($a, $b, ($container->services['request_stack'] ??= new \Symfony\Component\HttpFoundation\RequestStack()), new \Symfony\Component\HttpKernel\Controller\ArgumentResolver(new \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory(), new RewindableGenerator(function () use ($container) {
            yield 0 => ($container->privates['argument_resolver.backed_enum_resolver'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\BackedEnumValueResolver());
            yield 1 => ($container->privates['argument_resolver.datetime'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DateTimeValueResolver(NULL));
            yield 2 => ($container->privates['argument_resolver.request_attribute'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver());
            yield 3 => ($container->privates['argument_resolver.request'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver());
            yield 4 => ($container->privates['argument_resolver.session'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver());
            yield 5 => ($container->privates['argument_resolver.service'] ?? $container->load('getArgumentResolver_ServiceService'));
            yield 6 => ($container->privates['argument_resolver.default'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver());
            yield 7 => ($container->privates['argument_resolver.variadic'] ??= new \Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver());
        }, 8), new \Symfony\Component\DependencyInjection\Argument\ServiceLocator($container->getService ??= $container->getService(...), [
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestPayloadValueResolver' => ['privates', 'argument_resolver.request_payload', NULL, 'You can neither use "#[MapRequestPayload]" nor "#[MapQueryString]" since the Serializer component is not installed. Try running "composer require symfony/serializer-pack".'],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\QueryParameterValueResolver' => ['privates', 'argument_resolver.query_parameter_value_resolver', 'getArgumentResolver_QueryParameterValueResolverService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver' => ['privates', 'argument_resolver.backed_enum_resolver', 'getArgumentResolver_BackedEnumResolverService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver' => ['privates', 'argument_resolver.datetime', 'getArgumentResolver_DatetimeService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver' => ['privates', 'argument_resolver.request_attribute', 'getArgumentResolver_RequestAttributeService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver' => ['privates', 'argument_resolver.request', 'getArgumentResolver_RequestService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver' => ['privates', 'argument_resolver.session', 'getArgumentResolver_SessionService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver' => ['privates', 'argument_resolver.service', 'getArgumentResolver_ServiceService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver' => ['privates', 'argument_resolver.default', 'getArgumentResolver_DefaultService', true],
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver' => ['privates', 'argument_resolver.variadic', 'getArgumentResolver_VariadicService', true],
        ], [
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestPayloadValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestPayloadValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\QueryParameterValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\QueryParameterValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\BackedEnumValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DateTimeValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestAttributeValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\RequestValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\SessionValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\ServiceValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\DefaultValueResolver',
            'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver' => 'Symfony\\Component\\HttpKernel\\Controller\\ArgumentResolver\\VariadicValueResolver',
        ])), false);
    }

    /**
     * Gets the public 'request_stack' shared service.
     *
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    protected static function getRequestStackService($container)
    {
        return $container->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack();
    }

    /**
     * Gets the private 'exception_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\ErrorListener
     */
    protected static function getExceptionListenerService($container)
    {
        return $container->privates['exception_listener'] = new \Symfony\Component\HttpKernel\EventListener\ErrorListener(NULL, ($container->privates['logger'] ?? self::getLoggerService($container)), true, []);
    }

    /**
     * Gets the private 'locale_listener' shared service.
     *
     * @return \Symfony\Component\HttpKernel\EventListener\LocaleListener
     */
    protected static function getLocaleListenerService($container)
    {
        return $container->privates['locale_listener'] = new \Symfony\Component\HttpKernel\EventListener\LocaleListener(($container->services['request_stack'] ??= new \Symfony\Component\HttpFoundation\RequestStack()), 'en', NULL, false, []);
    }

    /**
     * Gets the private 'logger' shared service.
     *
     * @return \Symfony\Component\HttpKernel\Log\Logger
     */
    protected static function getLoggerService($container)
    {
        return $container->privates['logger'] = new \Symfony\Component\HttpKernel\Log\Logger(NULL, NULL, NULL, ($container->services['request_stack'] ??= new \Symfony\Component\HttpFoundation\RequestStack()), $container->getEnv('bool:default::key:web:default:kernel.runtime_mode:'));
    }

    public function getParameter(string $name): array|bool|string|int|float|\UnitEnum|null
    {
        if (isset($this->buildParameters[$name])) {
            return $this->buildParameters[$name];
        }

        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters))) {
            throw new ParameterNotFoundException($name);
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    public function hasParameter(string $name): bool
    {
        if (isset($this->buildParameters[$name])) {
            return true;
        }

        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || \array_key_exists($name, $this->parameters);
    }

    public function setParameter(string $name, $value): void
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    public function getParameterBag(): ParameterBagInterface
    {
        if (!isset($this->parameterBag)) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            foreach ($this->buildParameters as $name => $value) {
                $parameters[$name] = $value;
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }

        return $this->parameterBag;
    }

    private $loadedDynamicParameters = [
        'kernel.runtime_environment' => false,
        'kernel.runtime_mode' => false,
        'kernel.runtime_mode.web' => false,
        'kernel.runtime_mode.cli' => false,
        'kernel.runtime_mode.worker' => false,
        'kernel.build_dir' => false,
        'kernel.cache_dir' => false,
        'debug.file_link_format' => false,
        'debug.container.dump' => false,
    ];
    private $dynamicParameters = [];

    private function getDynamicParameter(string $name)
    {
        $container = $this;
        $value = match ($name) {
            'kernel.runtime_environment' => $container->getEnv('default:kernel.environment:APP_RUNTIME_ENV'),
            'kernel.runtime_mode' => $container->getEnv('query_string:default:container.runtime_mode:APP_RUNTIME_MODE'),
            'kernel.runtime_mode.web' => $container->getEnv('bool:default::key:web:default:kernel.runtime_mode:'),
            'kernel.runtime_mode.cli' => $container->getEnv('not:default:kernel.runtime_mode.web:'),
            'kernel.runtime_mode.worker' => $container->getEnv('bool:default::key:worker:default:kernel.runtime_mode:'),
            'kernel.build_dir' => $container->targetDir.'',
            'kernel.cache_dir' => $container->targetDir.'',
            'debug.file_link_format' => $container->getEnv('default::SYMFONY_IDE'),
            'debug.container.dump' => ($container->targetDir.''.'/AkyosUpdates_KernelDevDebugContainer.xml'),
            default => throw new ParameterNotFoundException($name),
        };
        $this->loadedDynamicParameters[$name] = true;

        return $this->dynamicParameters[$name] = $value;
    }

    protected function getDefaultParameters(): array
    {
        return [
            'kernel.project_dir' => \dirname(__DIR__, 4),
            'kernel.environment' => 'dev',
            'kernel.debug' => true,
            'kernel.logs_dir' => (\dirname(__DIR__, 3).'/log'),
            'kernel.bundles' => [
                'FrameworkBundle' => 'Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle',
                'TwigBundle' => 'Symfony\\Bundle\\TwigBundle\\TwigBundle',
            ],
            'kernel.bundles_metadata' => [
                'FrameworkBundle' => [
                    'path' => (\dirname(__DIR__, 4).'/vendor/symfony/framework-bundle'),
                    'namespace' => 'Symfony\\Bundle\\FrameworkBundle',
                ],
                'TwigBundle' => [
                    'path' => (\dirname(__DIR__, 4).'/vendor/symfony/twig-bundle'),
                    'namespace' => 'Symfony\\Bundle\\TwigBundle',
                ],
            ],
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => 'AkyosUpdates_KernelDevDebugContainer',
            'plugin_dir' => (\dirname(__DIR__, 4).'/'),
            'event_dispatcher.event_aliases' => [
                'Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent' => 'kernel.controller_arguments',
                'Symfony\\Component\\HttpKernel\\Event\\ControllerEvent' => 'kernel.controller',
                'Symfony\\Component\\HttpKernel\\Event\\ResponseEvent' => 'kernel.response',
                'Symfony\\Component\\HttpKernel\\Event\\FinishRequestEvent' => 'kernel.finish_request',
                'Symfony\\Component\\HttpKernel\\Event\\RequestEvent' => 'kernel.request',
                'Symfony\\Component\\HttpKernel\\Event\\ViewEvent' => 'kernel.view',
                'Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent' => 'kernel.exception',
                'Symfony\\Component\\HttpKernel\\Event\\TerminateEvent' => 'kernel.terminate',
            ],
            'fragment.renderer.hinclude.global_template' => NULL,
            'fragment.path' => '/_fragment',
            'kernel.secret' => 'frgegg5reg456g4re6g5e4r56',
            'kernel.http_method_override' => false,
            'kernel.trust_x_sendfile_type_header' => false,
            'kernel.trusted_hosts' => [

            ],
            'kernel.default_locale' => 'en',
            'kernel.enabled_locales' => [

            ],
            'kernel.error_controller' => NULL,
            'debug.error_handler.throw_at' => -1,
            'data_collector.templates' => [

            ],
            'twig.form.resources' => [
                0 => 'form_div_layout.html.twig',
                1 => 'bootstrap_4_layout.html.twig',
            ],
            'twig.default_path' => (\dirname(__DIR__, 4).'//templates'),
        ];
    }
}
