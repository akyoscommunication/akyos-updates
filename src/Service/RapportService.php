<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;
use AkyosUpdates\Trait\ServiceTrait;
use Symfony\Component\HttpFoundation\Request;

class RapportService
{
	use ServiceTrait;

	public function __construct()
	{
		$this->redirectRoute = 'akyos_updates_rapport';
	}

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

	public function pageSpeed()
	{
		$scores = get_option('akyos_updates_page_speed');

		if (!empty($scores)) {
			//tranform the scores into a message
			$message = '<p>Voici les scores de la page speed :</p> <br>';
			$message .= '<em>Desktop : </em><br>';
			$message .= '<ul>';
			$message .= '<li><p><strong>Performance : </strong>'.($scores['desktop']['performance'] * 100).' / 100%</p></li>';
			$message .= '<li><p><strong>Accessibilité : </strong>'.($scores['desktop']['accessibility'] * 100).' / 100%</p></li>';
			$message .= '<li><p><strong>Meilleures pratiques : </strong>'.($scores['desktop']['best_practices'] * 100).' / 100%</p></li>';
			$message .= '<li><p><strong>SEO : </strong>'.($scores['desktop']['seo'] * 100).' / 100%</p></li>';
			$message .= '</ul>';
		} else {
			$message = 'Pas de données pour le moment';
		}

		return [
			'message' => $message,
			'action_required' => true,
			'ajax' => [
				'hook' => 'admin_post_akyos_updates_page_speed',
				'success_message' => 'Réponse reçue avec succès'
			]
		];
	}

	#[Hook(hook: 'admin_post_akyos_updates_page_speed')]
	public function requestPageSpeed()
	{
		// get url site
		$url = get_site_url();

		$res = file_get_contents('https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url='.$url.'&key=AIzaSyDr7VTkop0l9S6q7HUIulETH31tEC2xOqA&category=ACCESSIBILITY&category=BEST_PRACTICES&category=PERFORMANCE&category=SEO');

		if ($res) {
			$res = json_decode($res);
			$scores = [
				'desktop' => [
					'performance' => $res->lighthouseResult->categories->performance->score,
					'accessibility' => $res->lighthouseResult->categories->accessibility->score,
					'best_practices' => $res->lighthouseResult->categories->{'best-practices'}->score,
					'seo' => $res->lighthouseResult->categories->seo->score,
				]
			];

			delete_option('akyos_updates_page_speed');
			add_option('akyos_updates_page_speed', $scores);
		}

		return wp_redirect(admin_url('admin.php?page='.$this->redirectRoute));
	}
}
