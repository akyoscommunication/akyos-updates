<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;
use AkyosUpdates\Service\DevChatService;

class DevChatController extends AbstractController
{
	public function __construct(
		private readonly DevChatService $devChatService
	)
	{
		parent::__construct();
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'Commentaires', menuTitle: 'Commentaires', capability: 'manage_options', slug: 'akyos_updates_comment', parentSlug: 'akyos_updates', position: 99)]
	public function index(): void
	{
		$this->render('dev_chat.html.twig', [
			'comments' => $this->devChatService->getComments()
		]);
	}
}
