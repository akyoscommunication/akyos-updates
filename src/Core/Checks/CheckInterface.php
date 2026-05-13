<?php

namespace AkyosUpdates\Core\Checks;

use AkyosUpdates\Core\Context\SiteContext;

interface CheckInterface
{
    public function getId(): string;

    public function getCategory(): string;

    public function getTitle(): string;

    public function getSuccessMessage(): string;

    public function run(SiteContext $context): CheckResult;
}
