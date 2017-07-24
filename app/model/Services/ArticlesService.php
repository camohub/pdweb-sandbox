<?php


namespace App\Model\Services;


use App;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;
use Kdyby;
use App\Model\Entity;
use Kdyby\Doctrine\EntityManager;
use Nette;
use Nette\Utils\Strings;
use Tracy\Debugger;


class ArticlesService extends Nette\Object
{

	/** @var Kdyby\Doctrine\EntityManager */
	public $em;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $langRepository;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $articleRepository;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $userRepository;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $articleLangRepository;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $categoryRepository;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $commentsArticlesRepository;

	/** @var Kdyby\Doctrine\EntityRepository */
	public $statusRepository;

	/** @var Nette\Security\User */
	protected $user;


	/**
	 * ArticlesService constructor.
	 * @param EntityManager $em
	 */
	public function __construct( EntityManager $em, Nette\Security\User $u )
	{
		$this->em = $em;
		$this->user = $u;

		$this->articleRepository = $em->getRepository( Entity\Article::class );
		$this->userRepository = $em->getRepository( Entity\User::class );
		$this->categoryRepository = $em->getRepository( Entity\CategoryArticle::class );
		$this->statusRepository = $em->getRepository( Entity\Status::class );
		$this->langRepository = $em->getRepository( Entity\Lang::class );
	}


	public function findBy( $by )
	{
		$articles = $this->articleRepository->createQueryBuilder()
			->select( 'article', 'user', 'status' )
			->from( 'App\Model\Entity\Article', 'article' )
			->innerJoin( 'article.status', 'status' )
			->innerJoin( 'article.user', 'user' )
			->innerJoin( 'article.langs', 'langs' )
			->whereCriteria( $by )
			->getQuery();

		// Next result is discarded. This is just re-hydrating the collections.
		$this->articleRepository->createQueryBuilder()
			->select( 'partial article.{id}', 'langs' )
			->from( 'App\Model\Entity\Article', 'article' )
			->innerJoin( 'article.status', 'status' )
			->innerJoin( 'article.user', 'user' )
			->innerJoin( 'article.langs', 'langs' )
			->whereCriteria( $by )
			->getQuery();

		// Returns ResultSet because of paginator.
		return new Kdyby\Doctrine\ResultSet( $articles );
	}


	/**
	 * @desc Datagrid can not make multi-step hydration via partial result like in $this->findBy() method.
	 * @param array $by
	 * @return mixed
	 */
	public function findByForDatagrid( array $by )
	{
		return $this->articleRepository->createQueryBuilder()
			->select( 'article', 'user', 'status', 'langs' )
			->from( 'App\Model\Entity\Article', 'article' )
			->innerJoin( 'article.status', 'status' )
			->innerJoin( 'article.user', 'user' )
			->innerJoin( 'article.langs', 'langs' )
			->innerJoin( 'langs.lang', 'lang', \Doctrine\ORM\Query\Expr\Join::WITH, 'lang.id = :lang' )
			->whereCriteria( $by )
			->setParameter( 'lang', Entity\Lang::SK );
	}


	/**
	 * @desc This "draft" article is created before user shows the article form first time,
	 * because uploaded images need to know article ID to create /uploads/ID directory.
	 * Otherwise we can't create article specific dir for images(used in TinyMCE images upload).
	 * @return mixed|Nette\Database\Table\ActiveRow
	 * @throws \Exception
	 */
	public function createDraft()
	{
		$langs = $this->langRepository->findBy( [], ['id' => 'ASC'] );

		$this->em->beginTransaction();

		try
		{
			$article = new App\Model\Entity\Article();
			$article->setUser( $this->userRepository->findOneBy( ['id' => $this->user->id] ) );
			$article->setStatus( $this->statusRepository->findOneBy( ['id' => Entity\Status::STATUS_DRAFT] ) );
			$article->addCategory( $this->categoryRepository->findOneBy( ['id' => Entity\CategoryArticle::CATEGORY_NEWS] ) );
			$this->em->persist( $article );
			$this->em->flush();  // Because we need new ID

			foreach ( $langs as $lang )
			{
				$article_lang = new App\Model\Entity\ArticleLang();
				$article_lang->setArticle( $article );
				$article_lang->setLang( $lang );
				$article_lang->setMetaDesc( '' );
				$article_lang->setTitle( 'DRAFT ' . $lang->getCode() . ' ' . time() );
				$article_lang->setPerex( '' );
				$article_lang->setContent( '' );
				$this->em->persist( $article_lang );
				$article->addLang( $article_lang );
			}

			$this->em->flush();
			$this->em->commit();
		}
		catch( \Exception $e )
		{
			$this->em->rollBack();
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			throw $e;
		}

		return $article;
	}


	/**
	 * @param $values array
	 * @param $id int
	 * @return int
	 * @throws App\Exceptions\DuplicateEntryException
	 * @throws App\Exceptions\GeneralException
	 */
	public function updateArticle( Array $values, $id )
	{

		$this->em->beginTransaction();
		try
		{
			$article = $this->articleRepository->find( $id );

			$values['categories'][] = Entity\CategoryArticle::CATEGORY_NEWS;
			$categories = $this->categoryRepository->findBy( ['id =' => $values['categories']] );
			$article->updateCategories( $categories );

			$article->setStatus( $this->statusRepository->find( $values['statuses_id'] ? Entity\Status::STATUS_PUBLISHED : Entity\Status::STATUS_UNPUBLISHED ) );

			foreach ( $article->getlangs() as $lang )  // getLangs() je prasačina, porušuje zapúzdrenie...
			{
				$lang->setMetaDesc( $values['meta_desc'][$lang->getCode()] );
				$lang->setTitle( $values['title'][$lang->getCode()] );
				$lang->setPerex( $values['perex'][$lang->getCode()] );
				$lang->setContent( $values['content'][$lang->getCode()] );

				$this->em->flush( $lang );  // Flushes only one concrete entity.
			}

			$this->em->flush( $article );  // Flushes only one concrete entity.
			$this->em->commit();
		}
		catch ( UniqueConstraintViolationException $e )
		{
			$this->em->rollback();
			throw new App\Exceptions\DuplicateEntryException( 'Článok so zadaným názvom už exituje. Názov musí byť v každnom jazyku unikátny.' );
		}
		catch ( \Exception $e )
		{
			$this->em->rollback();
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			throw new App\Exceptions\GeneralException( $e->getMessage() );
		}

	}


	/**
	 * @param Entity\Article $article
	 */
	public function switchVisibility( Entity\Article $article )
	{
		$status = $this->statusRepository->find( $article->getStatus()->getId() == Entity\Status::STATUS_PUBLISHED ? Entity\Status::STATUS_UNPUBLISHED : Entity\Status::STATUS_PUBLISHED );
		$article->setStatus( $status );
		$this->em->flush( $article );
	}


	/**
	 * @param $article Entity\Article
	 * @return int
	 */
	public function delete( $article )
	{
		$status = $this->statusRepository->findOneBy( ['id =' => Entity\Status::STATUS_DELETED] );
		$article->setStatus( $status );
		$this->em->flush( $article );
	}


}