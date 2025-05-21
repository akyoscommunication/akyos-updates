<?php

namespace AkyosUpdates\Controller;

use AkyosUpdates\Attribute\AdminRoute;
use AkyosUpdates\Class\AbstractController;

class ChatController extends AbstractController
{
	public function __construct(
	
	)
	{
		parent::__construct();
	}

	#[AdminRoute(type: AdminRoute::TYPE_SUBMENU_PAGE, pageTitle: 'AI Chat', menuTitle: 'AI Chat', capability: 'manage_options', slug: 'akyos_updates_chat', parentSlug: 'akyos_updates', position: 2)]
	public function aiChat(): void
	{
		$this->render('ai_chat.html.twig');
	}
}
