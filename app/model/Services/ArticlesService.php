<?php


namespace App\Model\Services;


use App;
use Nette;
use App\Model\Repositories\ArticlesCategoriesArticlesRepository;
use App\Model\Repositories\CategoriesArticlesRepository;
use App\Model\Repositories\CommentsArticlesRepository;
use App\Model\Repositories\ArticlesRepository;
use Nette\Utils\Strings;
use Tracy\Debugger;


class ArticlesService extends Nette\Object
{

	/** @var ArticlesRepository */
	protected $articlesRepository;

	/** @var CategoriesArticlesRepository */
	protected $categoriesArticlesRepository;

	/** @var ArticlesCategoriesArticlesRepository */
	protected $articlesCategoriesArticlesRepository;

	/** @var CommentsArticlesRepository */
	protected $commentsArticlesRepository;

	/** @var Nette\Security\User */
	protected $user;


	/**
	 * @param ArticlesRepository $aR
	 * @param CategoriesArticlesRepository $cAR
	 * @param ArticlesCategoriesArticlesRepository $aCAR
	 * @param CommentsArticlesRepository $comAR
	 * @param Nette\Security\User $u
	 */
	public function __construct( ArticlesRepository $aR, CategoriesArticlesRepository $cAR, ArticlesCategoriesArticlesRepository $aCAR, CommentsArticlesRepository $comAR, Nette\Security\User $u )
	{
		$this->articlesRepository = $aR;
		$this->categoriesArticlesRepository = $cAR;
		$this->articlesCategoriesArticlesRepository = $aCAR;
		$this->commentsArticlesRepository = $comAR;
		$this->user = $u;
	}


	/**
	 * @desc This "virtual" article is created before user shows the article form first time,
	 * because uploaded images need to know article ID to create /uploads/ID directory.
	 * Otherwise we can't create article specific dir for images(used in TinyMCE images upload).
	 * @return mixed|Nette\Database\Table\ActiveRow
	 */
	public function createVirtualArticle()
	{
		return $this->articlesRepository->insert([
			'acl_users_id' => $this->user->id,
			'meta_desc' => '',
			'title' => 'DRAFT ' . time(),
			'slug' => '',
			'perex' => '',
			'content' => '',
			'created' => Nette\Utils\DateTime::from( 'now' ),
			'articles_statuses_id' => ArticlesRepository::STATUS_VIRTUAL,
		])->id;
	}


	/**
	 * @param $values array
	 * @param $id int
	 * @throws App\Exceptions\InvalidArgumentException
	 * @throws App\Exceptions\GeneralException
	 * @return int
	 */
	public function updateArticle( Array $values, $id )
	{
		$values['categories'][] = CategoriesArticlesRepository::NEWS_CATEGORY;
		$categories = $values['categories'];

		$data['meta_desc'] = $values['meta_desc'];
		$data['title'] = $values['title'];
		$data['slug'] = Strings::webalize( $values['title'] );
		$data['perex'] = $values['perex'];
		$data['content'] = $values['content'];
		$data['articles_statuses_id'] = $values['articles_statuses_id'] ? ArticlesRepository::STATUS_PUBLISHED : ArticlesRepository::STATUS_UNPUBLISHED;

		$this->articlesRepository->getDatabase()->beginTransaction();
		try
		{
			$this->articlesCategoriesArticlesRepository->findBy( ['articles_id' => $id] )->delete();
			foreach ( $categories as $category )
			{
				$this->articlesCategoriesArticlesRepository->insert( ['articles_id' => $id, 'categories_articles_id' => $category] );
			}
			$this->articlesRepository->update( $id, $data );
		}
		catch ( \Exception $e )
		{
			$this->articlesRepository->getDatabase()->rollBack();
			throw new App\Exceptions\GeneralException( $e->getMessage() );
		}

		$this->articlesRepository->getDatabase()->commit();

	}


	/**
	 * @param $article
	 */
	public function switchVisibility( $article )
	{
		$this->articlesRepository->update( $article->id, [ 'articles_statuses_id' => $article->articles_statuses_id == ArticlesRepository::STATUS_PUBLISHED ? ArticlesRepository::STATUS_UNPUBLISHED : ArticlesRepository::STATUS_PUBLISHED ] );
	}



}