<?php

namespace AkyosUpdates\Core\Actions;

interface ActionInterface
{
    public function getId(): string;

    public function run(array $payload = []): ActionResult;
}
