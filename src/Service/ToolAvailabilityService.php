<?php

namespace AkyosUpdates\Service;

final class ToolAvailabilityService
{
    public static function probe(string $projectRootPath, string $wordpressRootPath): array
    {
        return [
            'wpCliAvailable' => self::detectWpCli($projectRootPath, $wordpressRootPath),
            'composerAvailable' => self::detectComposer($projectRootPath, $wordpressRootPath),
        ];
    }

    private static function detectWpCli(string $projectRoot, string $wpRoot): bool
    {
        $cwdCandidates = self::uniqueExistingDirs([$projectRoot, $wpRoot, ABSPATH]);
        $pharPaths = self::uniqueReadableFiles([
            self::joinPath($projectRoot, 'wp-cli.phar'),
            self::joinPath($projectRoot, 'wp.phar'),
            self::joinPath($wpRoot, 'wp-cli.phar'),
            self::joinPath($wpRoot, 'wp.phar'),
            self::joinPath(ABSPATH, 'wp-cli.phar'),
            self::joinPath(ABSPATH, 'wp.phar'),
        ]);

        foreach (['wp', 'wp-cli'] as $bin) {
            foreach ($cwdCandidates as $cwd) {
                $res = self::runProcess([$bin, '--version'], $cwd);
                if ($res !== null && $res['code'] === 0 && self::outputLooksLikeWpCli($res['out'])) {
                    return true;
                }
            }
        }

        $php = self::phpBinary();
        foreach ($pharPaths as $phar) {
            foreach ($cwdCandidates as $cwd) {
                $res = self::runProcess([$php, $phar, '--version'], $cwd);
                if ($res !== null && $res['code'] === 0 && self::outputLooksLikeWpCli($res['out'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function detectComposer(string $projectRoot, string $wpRoot): bool
    {
        $cwdCandidates = self::uniqueExistingDirs([$projectRoot, $wpRoot, ABSPATH]);
        $pharPaths = self::uniqueReadableFiles([
            self::joinPath($projectRoot, 'composer.phar'),
            self::joinPath($wpRoot, 'composer.phar'),
            self::joinPath(ABSPATH, 'composer.phar'),
        ]);

        foreach ($cwdCandidates as $cwd) {
            $res = self::runProcess(['composer', '--version'], $cwd);
            if ($res !== null && $res['code'] === 0 && self::outputLooksLikeComposer($res['out'])) {
                return true;
            }
        }

        $php = self::phpBinary();
        foreach ($pharPaths as $phar) {
            foreach ($cwdCandidates as $cwd) {
                $res = self::runProcess([$php, $phar, '--version'], $cwd);
                if ($res !== null && $res['code'] === 0 && self::outputLooksLikeComposer($res['out'])) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function outputLooksLikeWpCli(string $out): bool
    {
        $out = strtolower($out);

        return str_contains($out, 'wp-cli') || str_contains($out, 'wp cli');
    }

    private static function outputLooksLikeComposer(string $out): bool
    {
        $out = strtolower($out);

        return str_contains($out, 'composer') && str_contains($out, 'version');
    }

    private static function runProcess(array $command, string $cwd): ?array
    {
        if (!self::procOpenUsable()) {
            return null;
        }

        $workDir = is_dir($cwd) ? $cwd : null;
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptors, $pipes, $workDir, null, ['bypass_shell' => true]);
        if (!is_resource($process)) {
            return null;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $deadline = microtime(true) + 6.0;
        $exitCode = -1;

        while (true) {
            $stdout .= (string) stream_get_contents($pipes[1]);
            $stderr .= (string) stream_get_contents($pipes[2]);
            $status = proc_get_status($process);
            if ($status === false) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return null;
            }
            if (!$status['running']) {
                $exitCode = (int) ($status['exitcode'] ?? -1);
                break;
            }
            if (microtime(true) > $deadline) {
                proc_terminate($process, 9);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                return null;
            }
            usleep(45000);
        }

        $stdout .= (string) stream_get_contents($pipes[1]);
        $stderr .= (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        if ($exitCode === -1) {
            $exitCode = proc_close($process);
        } else {
            proc_close($process);
        }

        return [
            'code' => $exitCode,
            'out' => $stdout . $stderr,
        ];
    }

    private static function procOpenUsable(): bool
    {
        if (!function_exists('proc_open') || !function_exists('proc_close')) {
            return false;
        }

        $disabled = ini_get('disable_functions');
        if (!is_string($disabled) || $disabled === '') {
            return true;
        }

        $list = array_map('trim', explode(',', $disabled));

        return !in_array('proc_open', $list, true) && !in_array('proc_close', $list, true);
    }

    private static function phpBinary(): string
    {
        $b = PHP_BINARY;
        if (is_string($b) && $b !== '') {
            return $b;
        }

        return 'php';
    }

    private static function joinPath(string $dir, string $file): string
    {
        return trailingslashit($dir) . $file;
    }

    private static function uniqueExistingDirs(array $paths): array
    {
        $out = [];
        foreach ($paths as $p) {
            if (!is_string($p) || $p === '') {
                continue;
            }
            $real = realpath($p);
            $use = $real !== false ? $real : $p;
            if (!is_dir($use)) {
                continue;
            }
            if (!in_array($use, $out, true)) {
                $out[] = $use;
            }
        }

        return $out !== [] ? $out : self::abspathDirFallback();
    }

    private static function abspathDirFallback(): array
    {
        $ab = realpath(ABSPATH);

        return [($ab !== false ? $ab : rtrim(ABSPATH, '/\\'))];
    }

    private static function uniqueReadableFiles(array $paths): array
    {
        $out = [];
        foreach ($paths as $p) {
            if (!is_readable($p)) {
                continue;
            }
            $real = realpath($p);
            $use = $real !== false ? $real : $p;
            if (!in_array($use, $out, true)) {
                $out[] = $use;
            }
        }

        return $out;
    }
}
