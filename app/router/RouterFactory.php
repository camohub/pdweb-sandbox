<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList();

		$router[] = $adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Default:default');

		$router[] = $frontRouter = new RouteList('Front');
		$frontRouter[] = new Route('<presenter>/<action>[/<id>]', 'Default:default');

		return $router;
	}

}
