<?php


namespace App\Model\Services;


use App;
use App\Model\Entity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette;
use App\Model\Repositories\CommentsArticlesRepository;
use Tracy\Debugger;


class CommentsArticlesService extends Nette\Object
{

	/** @var EntityManager */
	protected $em;

	/** @var EntityRepository */
	protected $articleRepository;

	/** @var EntityRepository */
	protected $commentArticleRepository;

	/** @var EntityRepository */
	protected $userRepository;

	/** @var EntityRepository */
	protected $statusRepository;

	/** @var Nette\Security\User */
	protected $user;


	/**
	 * @param EntityManager $em
	 * @param Nette\Security\User $u
	 */
	public function __construct( EntityManager $em, Nette\Security\User $u )
	{
		$this->em = $em;
		$this->articleRepository = $em->getRepository( Entity\Article::class );
		$this->commentArticleRepository = $em->getRepository( Entity\CommentArticle::class );
		$this->userRepository = $em->getRepository( Entity\User::class );
		$this->statusRepository = $em->getRepository( Entity\Status::class );
		$this->user = $u;
	}


	/**
	 * @param Entity\Article $article
	 * @param $data
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertComment( Entity\Article $article, $data )
	{
		$data->content = htmlspecialchars( $data->content, ENT_QUOTES | ENT_HTML401 );
		$data->content = preg_replace( '/\*\*([^*]+)\*\*/', '<b>$1</b>', $data->content );
		$data->content = preg_replace( '/```\\n?([^`]+)```/', '<pre class="prettyprint custom"><code>$1</code></pre>', $data->content );

		$comment = new Entity\CommentArticle();
		$comment->setContent( $data['content'] );
		$comment->setArticle( $article );
		$comment->setUser( $user = $this->userRepository->find( $this->user->id ) );
		$comment->setStatus( $this->statusRepository->find( App\Model\Entity\Status::STATUS_PUBLISHED ) );
		$comment->setUserName( $user->getUserName() );
		$comment->setEmail( $user->getEmail() );

		$this->em->persist( $comment );
		$this->em->flush( $comment );

	}


	public function switchVisibility( $id )
	{
		$comment = $this->commentArticleRepository->find( $id );
		$status = $this->statusRepository->find( $comment->status == Entity\Status::STATUS_UNPUBLISHED ? Entity\Status::STATUS_PUBLISHED : Entity\Status::STATUS_UNPUBLISHED );
		$comment->setStatus( $status );
		$this->em->flush( $comment );
	}


}