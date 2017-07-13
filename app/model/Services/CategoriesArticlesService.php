<?php
namespace App\Model\Services;


use App;
use App\Model\Repositories\ArticlesRepository;
use Kdyby\Translation\Translator;
use Nette;
use Nette\Utils\Strings;
use App\Model\Repositories\CategoriesArticlesRepository;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use Tracy\Debugger;


class CategoriesArticlesService
{

	/** @var CategoriesArticlesRepository */
	protected $categoriesArticlesRepository;

	/** @var ArticlesCategoriesArticlesRepository */
	protected $articlesCategoriesArticlesRepository;

	/** @var ArticlesRepository */
	protected $articlesLangsRepository;

	/** @var Translator */
	protected $translator;


	/**
	 * @param CategoriesArticlesRepository $cAR
	 * @param ArticlesCategoriesArticlesRepository $aCAR
	 * @param ArticlesRepository $aR
	 */
	public function __construct( CategoriesArticlesRepository $cAR, ArticlesCategoriesArticlesRepository $aCAR, ArticlesRepository $aR, Translator $tr )
	{
		$this->categoriesArticlesRepository = $cAR;
		$this->articlesCategoriesArticlesRepository = $aCAR;
		$this->articlesRepository = $aR;
		$this->translator = $tr;
	}


	/**
	 * @param $id integer
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function switchVisibility( $id )
	{
		$row = $this->categoriesArticlesRepository->findOneBy( ['id' => (int)$id] );
		$row->update( ['visible' => $row->visible == 1 ? 0 : 1] );
		$this->categoriesArticlesRepository->cleanCache();

		return $row;
	}


	/**
	 * @desc Find ids of category and nested categories.
	 * @param int $id
	 * @param $ids array
	 * @return array
	 */
	public function findCategoryTreeIds( $id, array $ids = [] )
	{
		$ids[] = $id;

		if ( $children = $this->categoriesArticlesRepository->findBy( [ 'parent_id' => $id ] ) )
		{
			foreach ( $children as $child )
			{
				$ids = $this->findCategoryTreeIds( $child->id, $ids );
			}
		}

		return $ids;
	}


	/**
	 * @desc This method find all articles ids in blog_article_category which belongs to cat_ids
	 * @param int $id
	 * @return Nette\Database\Table\Selection
	 */
	public function findCategoryArticles( $id )
	{
		$cat_ids = $this->findCategoryTreeIds( $id );
		$art_ids = $this->articlesCategoriesArticlesRepository->findBy( ['categories_articles_id' => $cat_ids] )->fetchPairs( NULL, 'articles_id' );

		$articles = $this->articlesRepository
			->findBy( ['articles.id' => $art_ids, ':articles_langs.langs_code' => $this->translator->getLocale()] )
			->order( 'articles.id DESC' );

		return $articles;
	}


	/**
	 * @desc produces an array of categories in format required by form->select
	 * @param array $arr
	 * @param array $result
	 * @param int $lev
	 * @return array
	 */
	public function toSelect( $arr = [], $result = [], $lev = 0 )
	{
		if ( ! $arr )  // First call.
		{
			$arr = $this->categoriesArticlesRepository->findBy( [ 'parent_id' => NULL ] )->order( 'priority ASC' );
		}

		foreach ( $arr as $item )
		{
			if ( $item->id != 1 )  // 1 == Najnovšie and it is not optional value
			{
				$result[$item->id] = str_repeat( '>', $lev * 1 ) . ' ' .$item->name;
			}

			if ( $arr = $this->categoriesArticlesRepository->findBy( [ 'parent_id =' => $item->id ] )->order( 'priority ASC' ) )
			{
				$result = $this->toSelect( $arr, $result, $lev + 1 );
			}
		}

		return $result;
	}


	/**
	 * @desc Creats new cat. for blog module with specific params like url, module.
	 * @param $params
	 * @return Nette\Database\Table\ActiveRow
	 * @throws App\Exceptions\DuplicateEntryException
	 */
	public function createCategory( \ArrayAccess $params )
	{
		$params['slug'] = Strings::webalize( $params['name'] );
		$params['url'] = ':Articles:category';
		$params['url_params'] = $params['slug'];
		// If parent_id is not set or is 0 => NULL
		$params['parent_id'] = isset( $params['parent_id'] ) && $params['parent_id'] != 0 ? $params['parent_id'] : NULL;
		$params['visible'] = 1;
		$params['app'] = 0;
		$params['priority'] = 0;

		if ( $this->categoriesArticlesRepository->findOneBy( ['slug' => $params['slug']] ) )
		{
			throw new App\Exceptions\DuplicateEntryException( 'Kategória s názvom ' . $params['name'] . 'už existuje.', 1 );
		}

		$sameLevelCats = $this->categoriesArticlesRepository->findBy( [ 'parent_id' => $params['parent_id'] ] )->order( 'priority ASC' );
		foreach ( $sameLevelCats as $row )
		{
			$this->categoriesArticlesRepository->update( $row->id, ['priority' => $row->priority + 1] );
		}

		$category = $this->categoriesArticlesRepository->insert( $params );

		return $category;
	}


	/**
	 * @param $id int
	 * @param $name string
	 * @return Nette\Database\Table\ActiveRow
	 * @throws App\Exceptions\ItemNotFoundException
	 * @throws \Exception
	 */
	public function updateName( $id, $name )
	{
		$slug = $url_params = Strings::webalize( $name );

		try
		{
			return $this->categoriesArticlesRepository->update( $id, ['name' => $name, 'slug' => $slug] );
		}
		catch ( \PDOException $e )
		{
			// This catch ONLY checks duplicate entry to fields with UNIQUE KEY
			$info = $e->errorInfo;
			// mysql==1062  sqlite==19  postgresql==23505
			if ( $info[0] == 23000 && $info[1] == 1062 )
			{
				throw new App\Exceptions\DuplicateEntryException( 'Položka s rovnakým názvom už existuje' );
			}
			else { throw $e; }
		}

	}


	public function updatePriority( array $arr )
	{
		$categories = $this->categoriesArticlesRepository->findAll()->fetchPairs( 'id' );

		$i = 1;
		foreach ( $arr as $key => $val )
		{
			$row = $categories[(int)$key];
			$val = (int)$val == 0 ? NULL : (int)$val;

			// if the array is large it would be better to update only changed items
			if( $row->parent_id != $val || $row->priority != $i )
			{
				$row->update( ['parent_id' => $val, 'priority' => $i] );
			}
			$i++;
		}

		$this->categoriesArticlesRepository->cleanCache();
	}



	/**
	 * @param $id
	 * @return array
	 * @throws ContainsArticleException
	 * @throws PartOfAppException
	 * @throws \Exception
	 */
	public function delete( $id )
	{
		if( ! $row = $this->categoriesArticlesRepository->findOneBy( [ 'id' => (int) $id ] ) )
		{
			throw new NoArticleException( 'Article not found.' );
		}

		$result = $this->canDelete( $row );

		if ( isset( $result['app_error'] ) )
		{
			throw new PartOfAppException( 'Item can not be deleted because item ' . $result['app_error'] . ' is native part of application and can not be deleted.' );
		}
		if ( isset( $result['articles_error'] ) )
		{
			throw new ContainsArticleException( 'Item can not be deleted because item ' . $result['articles_error'] . ' contains one or more articles.' );
		}

		$this->categoriesArticlesRepository->cleanCache();

		$names = [];
		foreach ( $result['items'] as $row )
		{
			$names[] = $row->name;
			$row->delete();
		}

		return $names;
	}


//////Protected/Private///////////////////////////////////////////////////////

	/**
	 * @desc If $result contains app or article key, it means check is invalid ans can not be deleted.
	 * @param Nette\Database\Table\IRow $row
	 * @param array $result
	 * @return array
	 */
	protected function canDelete( Nette\Database\Table\IRow $row, $result = NULL )
	{
		$result = $result ?: [ 'items' => [] ];

		if ( $this->articlesCategoriesArticlesRepository->findBy( ['categories_articles_id' => $row->id] )->count() )
		{
			$result = [ 'articles_error' => $row->name ];
			return $result;
		}
		if ( $row->app )
		{
			$result = [ 'app_error' => $row->name ];
			return $result;
		}

		foreach ( $this->categoriesArticlesRepository->findBy( ['parent_id' => $row->id] ) as $child )
		{
			$result = $this->canDelete( $child, $result );
		}
		$result['items'][] = $row;

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