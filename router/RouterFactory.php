<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		//administrace
		$router[] = new Route('admin/<presenter>/<action>[/<id>]', [
			'module' => 'Admin',
			'presenter' => 'Dashboard',
			'action' => 'default',
			'id' => NULL
		]);
		
		$router[] = new Route('<presenter>/<action>[/<id>]', [
			'module' => 'Front',
			'presenter' => 'Homepage',
			'action' => 'default',
			'id' => NULL
		]);
		return $router;
	}

}
