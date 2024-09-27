<?php

namespace ContainerUmgQ1Iw;
include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ValueResolverInterface.php';
include_once \dirname(__DIR__, 4).'/vendor/symfony/http-kernel/Controller/ArgumentResolver/RequestPayloadValueResolver.php';

class RequestPayloadValueResolverGhostB22cd50 extends \Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver implements \Symfony\Component\VarExporter\LazyObjectInterface
{
    use \Symfony\Component\VarExporter\LazyGhostTrait;

    private const LAZY_OBJECT_PROPERTY_SCOPES = [
        "\0".parent::class."\0".'serializer' => [parent::class, 'serializer', parent::class],
        "\0".parent::class."\0".'translator' => [parent::class, 'translator', parent::class],
        "\0".parent::class."\0".'validator' => [parent::class, 'validator', parent::class],
        'serializer' => [parent::class, 'serializer', parent::class],
        'translator' => [parent::class, 'translator', parent::class],
        'validator' => [parent::class, 'validator', parent::class],
    ];
}

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

if (!\class_exists('RequestPayloadValueResolverGhostB22cd50', false)) {
    \class_alias(__NAMESPACE__.'\\RequestPayloadValueResolverGhostB22cd50', 'RequestPayloadValueResolverGhostB22cd50', false);
}
