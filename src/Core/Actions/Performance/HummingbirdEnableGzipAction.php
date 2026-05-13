<?php

namespace AkyosUpdates\Core\Actions;

final class HummingbirdEnableGzipAction implements ActionInterface
{
    public function getId(): string
    {
        return 'performance.hummingbird_enable_gzip';
    }

    public function run(array $payload = []): ActionResult
    {
        if (! class_exists('\Hummingbird\Core\Utils')) {
            return ActionResult::failure('Hummingbird non disponible.');
        }

        $gzipModule = \Hummingbird\Core\Utils::get_module('gzip');
        if (! is_object($gzipModule) || ! method_exists($gzipModule, 'enable') || ! method_exists($gzipModule, 'is_active')) {
            return ActionResult::failure('Module Gzip Hummingbird indisponible.');
        }

        $gzipModule->enable();

        $gzipActive = (bool) $gzipModule->is_active();
        $compressionType = (string) get_option('wphb_compression_type', '');

        $message = $gzipActive
            ? 'Compression Gzip Hummingbird activée.'
            : 'Impossible d’activer Gzip automatiquement. Vérifier la configuration serveur (Apache/Nginx).';
        $data = [
            'gzipActive' => $gzipActive,
            'compressionType' => $compressionType,
        ];
        if ($gzipActive) {
            return ActionResult::success($message, $data);
        }

        return ActionResult::failure($message, $data);
    }
}
