<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;
use Symfony\Component\HttpFoundation\Request;

class PathService
{
    use ServiceTrait;

    public function __construct() {}

    public function isBedrock(): bool
    {
        return file_exists(dirname(ABSPATH, 1) . '/wp-config.php');
    }

    public function getBedrockRoot(): string
    {
        return dirname(ABSPATH, 2);
    }

    public function getRootPath(): string
    {
        if ($this->isBedrock()) {
            return $this->getBedrockRoot();
        } else {
            return ABSPATH;
        }
    }

    public function getWordpressPath(): string
    {
        return ABSPATH;
    }

    public function getAppPath(): string
    {
        if($this->isBedrock()) {
            return $this->getRootPath() . '/web/app';
        } else {
            return $this->getRootPath() . '/wp-content';
        }
    }
}
