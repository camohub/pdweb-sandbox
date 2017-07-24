<?php


namespace App\FrontModule\Presenters;


use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\RepositoryFactory;
use Nette;
use App;
use Tracy\Debugger;


class ArticlesPresenter extends \App\Presenters\BasePresenter
{

	/** @var EntityManager @inject */
	public $em;

	/** @var  EntityRepository */
	public $categoryArticleRepository;

	/** @var EntityRepository */
	public $articleRepository;

	/** @var  App\Model\Services\CategoriesArticlesService @inject */
	public $categoriesArticlesService;

	/** @var  App\FrontModule\Forms\CommentFormFactory @inject */
	public $commentFormFactory;

	/** @var Nette\Caching\IStorage @inject */
	public $storage;

	/** @var  App\Model\Entity\Article */
	protected $article;


	public function startup()
	{
		parent::startup();

		$this->articleRepository = $this->em->getRepository( App\Model\Entity\Article::class );
		$this->categoryArticleRepository = $this->em->getRepository( App\Model\Entity\CategoryArticle::class );

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
		if ( $category = $this->categoryArticleRepository->findOneBy( [ 'langs.slug' => $title ] ) )  // Displays category.
		{
			$articles = $this->categoriesArticlesService->findCategoryArticles( $category );

			$this->template->articles = $this->setPaginator( $articles );
			$this->template->lang_code = $this->translator->getLocale();
			$this->setCategoryId( $category->getId() );
		}
		else // No category was found so try to find article.
		{
			// Do not call setCategoryId() because if there is a link to another article from other category,
			// it will highlight wrong category for that article.
			$article = $this->articleRepository->findOneBy( [ 'langs.slug' => $title ] );
			if ( ! $article )
			{
				throw new Nette\Application\BadRequestException( $this->translator->translate( 'front.articles.show.not-found' ), 404 );
			}

			$this->template->article = $article;
			$this->template->lang_code = $this->translator->getLocale();
		}

	}


/////Helpers/////////////////////////////////////////////////////////////////////////

	private function setPaginator( $articles )
	{
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = 5;

		//$paginator->itemCount = $articles->count( '*' );
		$articles->applyPaginator( $paginator );

		//$this->template->articles = $articles->limit( $paginator->itemsPerPage, $paginator->offset );
		return $articles;
	}



/////component/////////////////////////////////////////////////////////////////////////

	protected function createComponentCommentForm()
	{
		return $this->commentFormFactory->create();
	}


}
