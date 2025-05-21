<?php

namespace AkyosUpdates\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteApi
{
    public function __construct(
        public ?string $endpoint = '/',
        public ?string $method = 'GET',
    ) {
    }
}