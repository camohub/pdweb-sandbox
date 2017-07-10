<?php

namespace App\AdminModule\Presenters;


use Nette;
use App;
use Tracy\Debugger;


class GaleryPresenter extends App\AdminModule\Presenters\BasePresenter
{

	const ITEMS_PER_PAGE = 8;

	/** @var  App\Model\Repositories\UploadsArticlesRepository @inject */
	public $uploadsArticlesRepository;

	/** @var  App\AdminModule\Forms\ArticlesUploadFormFactory @inject */
	public $articleUploadFormFactory;

	/** @var  int */
	public $id = NULL;


	public function renderArticle( $id )
	{
		$this->id = $id;
		$images = $this->uploadsArticlesRepository->findBy( ['articles_id' => $id] )->order( 'id ASC' );
		$this->template->images = $this->setPaginator( $images );
		$this->template->page = $this['vp']->getPaginator()->page;  // Because of tinymce needs to know page.

	}


	public function renderProduct( $id )
	{
		$this->id = $id;
		$images = $this->images->uploadProductsRepository->findBy( ['products_id' => $id] )->order( 'id ASC' );
		$this->template->images = $this->setPaginator( $images );
		$this->template->page = $this['vp']->getPaginator()->page;  // Because of tinymce needs to know page.

	}


///////Protected/////////////////////////////////////////////////////////////

	protected function setPaginator( $images )
	{
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = self::ITEMS_PER_PAGE;
		$paginator->itemCount = $images->count();

		return $images->limit( $paginator->itemsPerPage, $paginator->offset );

	}

////// Components ////////////////////////////////////////////////////////////

	public function createComponentArticleUploadForm()
	{
		return $this->articleUploadFormFactory->create( $this->id );
	}

}
