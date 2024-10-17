<?php

namespace AkyosUpdates\Service;


use AkyosUpdates\Traits\ServiceTrait;

class HostingService
{
	use ServiceTrait;

	public function __construct()
	{

		$this->redirectRoute = 'akyos_updates_hosting';
	}

	public function getPhpVersion(): array
	{
		$phpVersion = phpversion();

		if (version_compare($phpVersion, '8.3.0', '<=')) {
			$phpVersionMessage = "<p>⭕ La version de PHP n'est pas à jour : $phpVersion</p>";
		} else {
			$phpVersionMessage = "<p>✅ La version de PHP est à jour : $phpVersion</p>";
		}

		return [
			'message' => $phpVersionMessage
		];
	}
}
