<?php

namespace AkyosUpdates\Core\Actions;

final class ActionResult
{
    /** @param array<string, mixed> $data */
    private function __construct(
        private bool $success,
        private string $message,
        private array $data = [],
        private ?string $code = null
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function success(string $message, array $data = []): self
    {
        return new self(true, $message, $data, null);
    }

    /** @param array<string, mixed> $data */
    public static function failure(string $message, array $data = [], ?string $code = null): self
    {
        return new self(false, $message, $data, $code);
    }

    /** @param array<string, mixed> $legacy */
    public static function fromLegacyArray(array $legacy): self
    {
        $success = (bool) ($legacy['success'] ?? false);
        $message = (string) ($legacy['message'] ?? '');
        $code = isset($legacy['code']) ? (string) $legacy['code'] : null;
        unset($legacy['success'], $legacy['message'], $legacy['code']);

        return new self($success, $message, $legacy, $code);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $out = [
            'success' => $this->success,
            'message' => $this->message,
        ];
        if ($this->code !== null && $this->code !== '') {
            $out['code'] = $this->code;
        }

        return array_merge($out, $this->data);
    }
}
