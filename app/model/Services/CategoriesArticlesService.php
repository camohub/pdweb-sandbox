<?php


namespace App\Model\Services;


use App;
use App\Model\Entity;
use App\Model\Repositories\ArticlesRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Doctrine\ResultSet;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Utils\Strings;
use App\Model\Repositories\CategoriesArticlesRepository;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use Tracy\Debugger;


class CategoriesArticlesService
{

	const CACHE = 'articles_categories_cache';
	const CACHE_TAG = 'articles_categories_cache_tag';


	/** @var EntityManager */
	protected $em;

	/** @var  @var Nette\Caching\IStorage */
	protected $storage;

	/** @var  @var Nette\Caching\Cache */
	public $cache;

	/** @var EntityRepository */
	public $categoryArticleRepository;

	/** @var EntityRepository */
	public $statusRepository;

	/** @var EntityRepository */
	public $langRepository;

	/** @var Translator */
	protected $translator;


	/**
	 * @param EntityManager $em
	 * @param Translator $tr
	 */
	public function __construct( EntityManager $em, Nette\Caching\IStorage $s, Translator $tr )
	{
		$this->em = $em;
		$this->storage = $s;
		$this->cache = new Nette\Caching\Cache( $this->storage, self::CACHE );
		$this->categoryArticleRepository = $em->getRepository( Entity\CategoryArticle::class );
		$this->categoryArticleLangRepository = $em->getRepository( Entity\CategoryArticleLang::class );
		$this->statusRepository = $em->getRepository( Entity\Status::class );
		$this->langRepository = $em->getRepository( Entity\Lang::class );
		$this->translator = $tr;
	}


	/**
	 * @param $category
	 * @return mixed
	 */
	public function switchVisibility( $category )
	{
		$status = $this->statusRepository->find( $category->getStatus()->getId() == Entity\Status::STATUS_PUBLISHED ? Entity\Status::STATUS_UNPUBLISHED : Entity\Status::STATUS_PUBLISHED );
		$category->setStatus( $status );
		$this->em->flush( $category );

		$this->cleanCache();

		return $category;
	}


	/**
	 * @desc Find ids of category and nested categories.
	 * @param Entity\CategoryArticle $category
	 * @param $ids array
	 * @return array
	 */
	public function findCategoryTreeIds( Entity\CategoryArticle $category, array $ids = [] )
	{
		$ids[] = $category->getId();

		if ( $children = $this->categoryArticleRepository->findBy( [ 'parent_id' => $category->getId() ] ) )
		{
			foreach ( $children as $child )
			{
				$ids = $this->findCategoryTreeIds( $child, $ids );
			}
		}

		return $ids;
	}


	/**
	 * @desc This method find all articles ids in blog_article_category which belongs to cat_ids
	 * @param Entity\CategoryArticle $category
	 * @return ResultSet
	 */
	public function findCategoryArticles( $category )
	{
		if( is_numeric( $category ) ) $category = $this->categoryArticleRepository->find( $category );

		$cat_ids = $this->findCategoryTreeIds( $category );

		$criteria = [ 'categories.id' => $cat_ids ];
		$articles = $this->categoryArticleRepository->createQueryBuilder()
			->select( 'a' )
			->from( 'App\Model\Entity\Article', 'a' )
			->whereCriteria( $criteria )
			->orderBy( 'a.created', 'DESC' )
			->getQuery();

		// Returns ResultSet because of paginator.
		return new ResultSet( $articles );
	}


	/**
	 * @desc produces an array of categories in format required by form->select
	 * @param array $arr
	 * @param array $result
	 * @param int $lev
	 * @return array
	 */
	public function categoriesToSelect( $arr = [], $result = [], $lev = 0 )
	{
		if ( ! $arr )  // First call.
		{
			$arr = $this->categoryArticleRepository->findBy( [ 'parent_id =' => NULL ], [ 'priority' => 'ASC' ] );
		}

		foreach ( $arr as $item )
		{
			if ( $item->getId() != Entity\CategoryArticle::CATEGORY_NEWS )  // Najnovšie is not optional value
			{
				$result[$item->getId()] = str_repeat( '>', $lev * 1 ) . $item->getDefaultLang()->getTitle();
			}

			if ( $arr = $this->categoryArticleRepository->findBy( [ 'parent_id =' => $item->getId() ], [ 'priority' => 'ASC' ] ) )
			{
				$result = $this->categoriesToSelect( $arr, $result, $lev + 1 );
			}
		}

		return $result;
	}


	/**
	 * @param \ArrayAccess $params
	 * @return Entity\CategoryArticle
	 * @throws App\Exceptions\DuplicateEntryException
	 * @throws \Exception
	 */
	public function createCategory( \ArrayAccess $params )
	{
		$this->em->beginTransaction();

		try
		{
			$same_level_categories = $this->categoryArticleRepository->findBy( [ 'parent_id' => $params['parent_id'] ] );
			foreach ( $same_level_categories as $cat )
			{
				$cat->setPriority( $cat->getPriority() + 1 );
			}
			$this->em->flush( $same_level_categories );


			$category = new Entity\CategoryArticle();
			$this->em->persist( $category );
			$category->setParent( isset( $params['parent_id'] ) ? $this->categoryArticleRepository->find( $params['parent_id'] ) : NULL );
			$category->setStatus( $this->statusRepository->find( Entity\Status::STATUS_UNPUBLISHED ) );
			$category->setUrl( ':Front:Articles:show' );
			$this->em->flush( $category );


			$langs = $this->langRepository->findBy( [], ['id' => 'ASC'] );
			foreach ( $langs as $lang )
			{
				$category_lang = new App\Model\Entity\CategoryArticleLang();
				$this->em->persist( $category_lang );
				$category_lang->setCategory( $category );
				$category_lang->setLang( $lang );
				$category_lang->setTitle( $params['titles'][$lang->getCode()] );
				$category->addLang( $category_lang );
				$this->em->flush( $category_lang );
			}

		}
		catch ( UniqueConstraintViolationException $e )
		{
			$this->em->rollback();
			throw new App\Exceptions\DuplicateEntryException( 'Kategória so zadaným názvom už exituje. Názov musí byť unikátny pre každý jazyk.' );
		}
		catch ( \Exception $e )
		{
			$this->em->rollback();
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			throw $e;
		}

		$this->em->commit();
		return $category;
	}


	/**
	 * @param $id
	 * @param array $names
	 * @return null|object
	 * @throws App\Exceptions\DuplicateEntryException
	 */
	public function updateTitle( $id, array $names )
	{
		// No need transaction...
		try
		{
			$category = $this->categoryArticleRepository->find( $id );
			foreach ( $category->getLangs() as $lang )
			{
				$lang->setTitle( $names[$lang->getCode()] );
				$this->em->flush( $lang );
			}
		}
		catch ( UniqueConstraintViolationException $e )
		{
			throw new App\Exceptions\DuplicateEntryException( 'Kategória s rovnakým názvom už exituje. Názov musí byť v každnom jazyku unikátny.' );
		}

		return $category;

	}


	/**
	 * @param array $arr
	 * @return bool
	 */
	public function updatePriority( array $arr )
	{
		$pairs = $this->categoryArticleRepository->findAssoc( 'id' );
		$i = 1;
		foreach ( $arr as $key => $val )
		{
			// if the array is large it would be better to update only changed items
			$pairs[(int) $key]->setParentId( $val == 0 ? NULL : (int) $val );
			$pairs[(int) $key]->setPriority( $i );
			$i++;
		}

		$this->em->flush();
		$this->cleanCache();

		return true;
	}


	/**
	 * @param $category
	 * @return array
	 * @throws ContainsArticleException
	 * @throws NoCategoryException
	 * @throws PartOfAppException
	 */
	public function delete( Entity\CategoryArticle $category )
	{
		if( ! $category )
		{
			throw new NoCategoryException( 'Category not found.' );
		}

		$result = $this->canDelete( $category );

		if ( isset( $result['app_error'] ) )
		{
			throw new PartOfAppException( 'Category can not be deleted because category ' . $result['app_error'] . ' is native part of application and can not be deleted.' );
		}
		if ( isset( $result['articles_error'] ) )
		{
			throw new ContainsArticleException( 'Category can not be deleted because category ' . $result['articles_error'] . ' contains one or more articles.' );
		}

		$names = [];

		foreach ( $result['items'] as $item )
		{
			$names[] = $item->getDefaultLang()->getTitle();
			$this->em->remove( $item );
		}

		$this->em->flush();

		$this->cleanCache();

		return $names;

	}


	public function cleanCache()
	{
		$langs = $this->langRepository->findAll();
		foreach ( $langs as $lang )
		{
			$this->cache->clean( [ Nette\Caching\Cache::TAGS => [ self::CACHE_TAG . $lang->getCode()/*, 'is_in_cache'*/ ] ] );
		}

	}


//////Protected/Private///////////////////////////////////////////////////////

	/**
	 * @param Entity\CategoryArticle $category
	 * @param null $result
	 * @return array|null
	 */
	protected function canDelete( Entity\CategoryArticle $category, $result = NULL )
	{
		$result = $result ?: [ 'items' => [] ];

		if ( $category->getArticles()->count() )
		{
			$result = [ 'articles_error' => $category->getDefaultLang()->getTitle() ];
			return $result;
		}
		if ( $category->getApp() )
		{
			$result = [ 'app_error' => $category->getDefaultLang()->getTitle() ];
			return $result;
		}

		foreach ( $category->getChildren() as $child )
		{
			$result = $this->canDelete( $child, $result );
		}

		$result['items'][] = $category;

		return $result;

	}


}

class PartOfAppException extends \Exception
{
	// Entity or nested entity is part of application and so it can not be deleted
}

class ContainsArticleException extends \Exception
{
	// Entity or nested entity contains one or more articles and so it can not be deleted.
}

class NoArticleException extends \Exception
{
	// Entity not found.
}

class NoCategoryException extends \Exception
{
	// Entity not found.
}