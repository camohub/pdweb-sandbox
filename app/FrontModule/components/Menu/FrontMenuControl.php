<?php

namespace App\FrontModule\Components\Menu;


use DK;
use Tracy\Debugger;


class FrontMenuControl extends DK\Menu\UI\Control
{

	public function renderTop()
	{
		$this->template->setFile( __DIR__ . '/Top.latte' );
		$this->template->menu = $this->getMenu();
		$this->template->render();
	}

}


interface IFrontMenuControlFactory
{

	/**
	* @return \App\FrontModule\Components\Menu\FrontMenuControl
	*/
	public function create();

}