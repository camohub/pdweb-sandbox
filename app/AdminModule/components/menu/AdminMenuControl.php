<?php

namespace App\AdminModule\Components\Menu;

use DK;

class AdminMenuControl extends DK\Menu\UI\Control {}


interface IAdminMenuControlFactory
{
	/**
	 * @return \App\AdminModule\Components\Menu\AdminMenuControl
	 */
	public function create();
}
