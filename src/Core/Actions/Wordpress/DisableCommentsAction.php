<?php

namespace AkyosUpdates\Core\Actions;

final class DisableCommentsAction implements ActionInterface
{
    public function getId(): string
    {
        return 'wordpress.disable_comments';
    }

    public function run(array $payload = []): ActionResult
    {
        update_option('default_comment_status', 'closed');
        update_option('default_ping_status', 'closed');
        update_option('comment_registration', 0);
        update_option('close_comments_for_old_posts', 1);

        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed' WHERE post_status IN ('publish','future','draft','pending','private')");

        return ActionResult::success('Commentaires désactivés et contenus existants fermés.');
    }
}
