<?php

namespace AkyosUpdates\Core\Checks\WordPress;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;

final class PostByEmailDisabledCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'wordpress.post_by_email_disabled';
    }

    public function getCategory(): string
    {
        return 'WordPress';
    }

    public function getTitle(): string
    {
        return 'Publication par mail';
    }
    public function getSuccessMessage(): string
    {
        return 'Publication par mail désactivée.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $mailServer = trim((string) get_option('mailserver_url', ''));
        $mailLogin = trim((string) get_option('mailserver_login', ''));
        $mailPass = trim((string) get_option('mailserver_pass', ''));
        $mailPort = (int) get_option('mailserver_port', 110);

        $isDisabled = $mailServer === '' && $mailLogin === '' && $mailPass === '' && $mailPort === 110;

        if ($isDisabled) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                $this->getSuccessMessage()
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'medium',
            'Configuration publication par mail active ou incomplète.',
            true,
            'wordpress.disable_post_by_email'
        );
    }
}
