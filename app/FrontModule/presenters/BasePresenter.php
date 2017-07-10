<?php


namespace App\FrontModule\Presenters;


use App;
use Nette\Http\SessionSection;


abstract class BasePresenter extends App\Presenters\BasePresenter
{

	/** @var SessionSection */
	public $sessionCategoriesArticles;

	/** @var SessionSection */
	public $sessionCategoriesProducts;


	public function startup()
	{
		parent::startup();

		$this->sessionCategoriesArticles = $this->getSession( 'categoriesArticles' );
		$this->sessionCategoriesArticles = $this->getSession( 'categoriesProducts' );
	}

}
