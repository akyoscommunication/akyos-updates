<?php

namespace AkyosUpdates\Traits;

trait ServiceTrait
{
	private string $redirectRoute;


	public function getRedirectRoute(): string
	{
		return $this->redirectRoute;
	}

	public function setRedirectRoute(string $redirectRoute): void
	{
		$this->redirectRoute = $redirectRoute;
	}
}
