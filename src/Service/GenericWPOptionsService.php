<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;

class GenericWPOptionsService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_generic_wp_options';
	}

	public function getCommentsStatus(): array
    {
        $commentsDisabled = get_option('default_comment_status');

        if($commentsDisabled !== 'closed') {
            $commentsDisabledMessage = "<p>⭕ Les commentaires sont activés</p>";
        } else {
            $commentsDisabledMessage = "<p>✅ Les commentaires sont désactivés</p>";
        }

        return [
            'message' => $commentsDisabledMessage,
            'action_required' => $commentsDisabled !== 'closed'
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_disable_comments')]
    public function disableComments(): bool
    {
        update_option('default_comment_status', 'closed');
        update_option('comment_registration', 0);
        update_option('comment_moderation', 1);
        update_option('comment_previously_approved', 0);
        update_option('show_avatars', 0);

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
    }

    public function getMailPublisherStatus(): array
    {
        $mailServerUrl = get_option('mailserver_url');
        $mailServerPort = get_option('mailserver_port');
        $mailServerLogin = get_option('mailserver_login');
        $mailServerPass = get_option('mailserver_pass');

        if($mailServerUrl !== '' && $mailServerPort !== '' && $mailServerLogin !== '' && $mailServerPass !== '') {
            $mailServerMessage = "<p>⭕ La publication par mail est activée (url: $mailServerUrl, port: $mailServerPort, login: $mailServerLogin, pass: $mailServerPass)</p>";
        } else {
            $mailServerMessage = "<p>✅ La publication par mail est désactivée</p>";
        }

        return [
            'message' => $mailServerMessage,
            'action_required' => $mailServerUrl !== '' && $mailServerPort !== '' && $mailServerLogin !== '' && $mailServerPass !== ''
        ];
    }

    #[Hook(hook: 'admin_post_akyos_updates_disable_mail_publisher')]
    public function disableMailPublisher(): bool
    {
        update_option('mailserver_url', '');
        update_option('mailserver_port', '');
        update_option('mailserver_login', '');
        update_option('mailserver_pass', '');

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
    }
}
