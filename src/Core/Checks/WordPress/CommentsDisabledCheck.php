<?php

namespace AkyosUpdates\Core\Checks\WordPress;

use AkyosUpdates\Core\Checks\CheckInterface;
use AkyosUpdates\Core\Checks\CheckResult;
use AkyosUpdates\Core\Context\SiteContext;
use WP_Query;

final class CommentsDisabledCheck implements CheckInterface
{
    public function getId(): string
    {
        return 'wordpress.comments_disabled';
    }

    public function getCategory(): string
    {
        return 'WordPress';
    }

    public function getTitle(): string
    {
        return 'Commentaires';
    }
    public function getSuccessMessage(): string
    {
        return 'Commentaires désactivés globalement.';
    }


    public function run(SiteContext $context): CheckResult
    {
        $defaultStatus = get_option('default_comment_status');
        $openCount = (new WP_Query([
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'comment_status' => 'open',
            'no_found_rows' => true,
        ]))->found_posts;

        $isDisabled = $defaultStatus === 'closed' && $openCount === 0;

        if ($isDisabled) {
            return new CheckResult(
                $this->getId(),
                $this->getCategory(),
                $this->getTitle(),
                'ok',
                'success',
                'Commentaires désactivés globalement.'
            );
        }

        return new CheckResult(
            $this->getId(),
            $this->getCategory(),
            $this->getTitle(),
            'fail',
            'high',
            'Commentaires encore actifs sur le site.',
            true,
            'wordpress.disable_comments'
        );
    }
}
