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

		$frontRouter[] = new Route( '[<locale sk|en>/][<presenter articles>/]<title>[/<action>/<vp-page \d+>]',
			[
				'locale' => [
					Route::VALUE => 'sk',
				],
				'presenter' => [
					Route::VALUE => 'Articles',
					Route::FILTER_TABLE => [
						'clanky' => 'Articles',
					],
				],
				'action' => [
					Route::VALUE => 'show',
					Route::FILTER_TABLE => [
						'strana' => 'show',
					],
				],
				'title' => [
					Route::VALUE => 'najnovsie-clanky',
				],
				'vp-page' => [
					Route::VALUE => '1',
				],
			]
		);

		$frontRouter[] = new Route( '[<locale=sk sk|en>/]<presenter>/<action>[/<id>]' );

		return $router;
	}

}
