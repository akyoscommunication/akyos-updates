<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;
use Symfony\Component\HttpFoundation\Request;

class DevChatService
{
	public function getComments()
	{
		$comments = get_option('akyos_updates_comments');


		if (!$comments) {
			add_option('akyos_updates_comments', []);
		}

		$message = '<p>Voici les liste des commentaires :</p> <br>';
		if ($comments) {
			$message .= implode('<br> ', array_column($comments, 'content'));
		}

		return [
			'message' => $message,
			'action_required' => true,
			'fields' => [
				[
					'label' => 'Commentaire',
					'name' => 'comment',
					'type' => 'text',
				]
			],
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_add_comment',
			]
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_add_comment')]
	public function addComments()
	{
		$request = Request::createFromGlobals();
		$comment = $request->request->get('comment');

		$comments = get_option('akyos_updates_comments');

		$comments[] = [
			'content' => $comment,
			'date' => date('Y-m-d H:i:s')
		];

		update_option('akyos_updates_comments', $comments);
	}
}
