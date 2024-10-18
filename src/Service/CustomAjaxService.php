<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Attribute\Hook;
use Symfony\Component\HttpFoundation\Request;

class CustomAjaxService
{
	#[Hook(hook: 'wp_ajax_akyos_updates_custom_ajax')]
	public function customAjax()
	{
		$request = Request::createFromGlobals();
		$param = $request->request->all();

		if (!$param['hook']) {
			wp_send_json_error('Erreur lors de la récupération des données');
		}

		do_action($param['hook']);

		wp_send_json_success('success');
	}
}
