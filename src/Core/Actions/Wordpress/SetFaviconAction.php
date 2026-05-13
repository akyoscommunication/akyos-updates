<?php

namespace AkyosUpdates\Core\Actions;

final class SetFaviconAction implements ActionInterface
{
    public function getId(): string
    {
        return 'wordpress.set_favicon';
    }

    public function run(array $payload = []): ActionResult
    {
        $attachmentId = isset($payload['attachmentId']) ? (int) $payload['attachmentId'] : 0;
        if ($attachmentId <= 0) {
            return ActionResult::failure('attachmentId invalide.');
        }

        $exists = wp_get_attachment_image_src($attachmentId, 'full');
        if (! is_array($exists) || empty($exists[0])) {
            return ActionResult::failure('Média introuvable ou non image.');
        }

        update_option('site_icon', $attachmentId);

        return ActionResult::success('Favicon mis à jour.', [
            'siteIconId' => $attachmentId,
            'faviconUrl' => (string) $exists[0],
        ]);
    }
}
