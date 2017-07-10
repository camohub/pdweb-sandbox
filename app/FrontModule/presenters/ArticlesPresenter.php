<?php
namespace App\FrontModule\Presenters;

use    Nette;
use    App;
use    App\Model;
use    Tracy\Debugger;


class ArticlesPresenter extends \App\Presenters\BasePresenter
{


	/** @var Nette\Caching\IStorage @inject */
	public $storage;

	/** @var  App\Model\Services\CategoriesArticlesService @inject */
	public $categoriesArticlesService;

	/** @var  App\Model\Repositories\CategoriesArticlesRepository @inject */
	public $categoriesArticlesRepository;

	/** @var  App\Model\Repositories\ArticlesRepository @inject */
	public $articlesRepository;

	/** @var  App\Model\Repositories\CommentsArticlesRepository @inject */
	public $commentsArticlesRepository;

	/** @var  App\FrontModule\Forms\CommentFormFactory @inject */
	public $commentFormFactory;

	/** @var  Nette\Database\IRow */
	protected $article;


	public function startup()
	{
		parent::startup();


	}


	/**
	 * @desc This method is used for both categories and articles.
	 * Because of URL contains only SLUG which can be slug of category or article.
	 * And this is because if article belongs to more categories its URL will generate content duplicities.
	 * @param $title
	 * @throws Nette\Application\BadRequestException
	 */
	public function renderShow( $title )
	{
		if ( $category = $this->categoriesArticlesRepository->findOneBy( [ 'slug' => $title ] ) )  // Displays category.
		{
			$articles = $this->categoriesArticlesService->findCategoryArticles( $category->id );

			$this->template->articles = $this->setPaginator( $articles );
			$this->setCategoryId( $category->id );
		}
		else // No category was found so try to find article.
		{
			// Do not call setCategoryId() because if there is a link to another article from other category,
			// it will highlight wrong category for that article.
			if ( ! $article = $this->articlesRepository->findOneBy( [ 'slug' => $title ] ) )
			{
				throw new Nette\Application\BadRequestException( 'Požadovanú stránku sa nepodarilo nájsť.', 404 );
			}

			$this->template->article = $article;
			$this->template->comments = $this->commentsArticlesRepository->findBy( ['articles_id' => $article->id] )->order( 'id ASC' );
			$this->template->commentsArticlesRepository = $this->commentsArticlesRepository;

		}

	}


/////Helpers/////////////////////////////////////////////////////////////////////////

	private function setPaginator( $articles )
	{
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 2;

		$paginator->itemCount = $articles->count( '*' );

		$this->template->articles = $articles->limit( $paginator->itemsPerPage, $paginator->offset );

		return $articles;

	}



/////component/////////////////////////////////////////////////////////////////////////

	protected function createComponentCommentForm()
	{
		return $this->commentFormFactory->create();
	}


}
