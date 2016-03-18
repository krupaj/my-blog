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
		//administrace ADMIN modul
		$router[] = new Route('admin/<presenter>/<action>[/<id>]', [
			'module' => 'Admin',
			'presenter' => 'Dashboard',
			'action' => 'default',
			'id' => NULL
		]);
		//verejna cast FRONT modul
		$router[] = new Route('post/<id>', 'Front:Homepage:post');
		$router[] = new Route('tag/<id>', 'Front:Homepage:tag');
		$router[] = new Route('terms', 'Front:Homepage:terms');
		$router[] = new Route('project', 'Front:Homepage:project');
		$router[] = new Route('<presenter>/<action>[/<id>]', [
			'module' => 'Front',
			'presenter' => 'Homepage',
			'action' => 'default',
			'id' => NULL
		]);
		return $router;
	}

}
