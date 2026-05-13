<?php

namespace AkyosUpdates\Service;

use Throwable;
use WP_Error;
use WP_REST_Response;

final class JsonService
{
    public static function wrap(callable $callback): WP_REST_Response|WP_Error
    {
        $baseLevel = ob_get_level();
        ob_start();
        try {
            $data = $callback();
        } catch (Throwable $e) {
            while (ob_get_level() > $baseLevel) {
                ob_end_clean();
            }

            $message = (defined('WP_DEBUG') && WP_DEBUG)
                ? $e->getMessage()
                : 'Une erreur interne est survenue.';

            $data = ['status' => 500];
            if ((defined('WP_DEBUG') && WP_DEBUG)
                || (defined('AKYOS_UPDATES_REST_DEBUG') && constant('AKYOS_UPDATES_REST_DEBUG'))) {
                $data['file'] = $e->getFile();
                $data['line'] = $e->getLine();
            }

            return new WP_Error('akyos_updates_exception', $message, $data);
        }

        while (ob_get_level() > $baseLevel) {
            ob_end_clean();
        }

        if ($data instanceof WP_Error) {
            return $data;
        }

        $data = self::mixed($data);
        if (wp_json_encode($data) === false) {
            return new WP_Error('akyos_updates_json_encode', 'Encodage de la réponse impossible.', ['status' => 500]);
        }

        return new WP_REST_Response($data);
    }

    public static function mixed(mixed $value): mixed
    {
        if (is_resource($value)) {
            return null;
        }

        if (is_string($value)) {
            $clean = wp_check_invalid_utf8($value, true);

            return is_string($clean) ? $clean : '';
        }

        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $nk = is_string($k) ? (wp_check_invalid_utf8($k, true) ?: 'key') : $k;
                $out[$nk] = self::mixed($v);
            }

            return $out;
        }

        if (is_float($value) && ! is_finite($value)) {
            return null;
        }

        if (is_object($value)) {
            $encoded = wp_json_encode($value);
            if ($encoded === false) {
                return null;
            }

            $decoded = json_decode($encoded, true);

            return is_array($decoded) ? self::mixed($decoded) : null;
        }

        return $value;
    }
}
