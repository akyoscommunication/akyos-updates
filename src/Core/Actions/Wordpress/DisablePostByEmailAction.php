<?php

namespace AkyosUpdates\Core\Actions;

final class DisablePostByEmailAction implements ActionInterface
{
    public function getId(): string
    {
        return 'wordpress.disable_post_by_email';
    }

    public function run(array $payload = []): ActionResult
    {
        update_option('mailserver_url', '');
        update_option('mailserver_login', '');
        update_option('mailserver_pass', '');
        update_option('mailserver_port', 110);

        return ActionResult::success('Publication par mail neutralisée.');
    }
}
