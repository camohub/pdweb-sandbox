<?php


namespace App\Model\Services;


use App;
use Nette;
use App\Model\Repositories\CommentsArticlesRepository;
use App\Model\Repositories\ArticlesRepository;
use Tracy\Debugger;


class CommentsArticlesService extends Nette\Object
{

	/** @var ArticlesRepository */
	protected $articlesRepository;

	/** @var CommentsArticlesRepository */
	protected $commentsArticlesRepository;

	/** @var Nette\Security\User */
	protected $user;


	/**
	 * @param ArticlesRepository $aR
	 * @param CommentsArticlesRepository $cAR
	 * @param Nette\Security\User $u
	 */
	public function __construct( ArticlesRepository $aR, CommentsArticlesRepository $cAR, Nette\Security\User $u )
	{
		$this->articlesRepository = $aR;
		$this->commentsArticlesRepository = $cAR;
		$this->user = $u;
	}


	/**
	 * @param $article_id
	 * @param $data
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function insertComment( $article_id, $data )
	{
		$data->content = htmlspecialchars( $data->content, ENT_QUOTES | ENT_HTML401 );
		$data->content = preg_replace( '/\*\*([^*]+)\*\*/', '<b>$1</b>', $data->content );
		$data->content = preg_replace( '/```\\n?([^`]+)```/', '<pre class="prettyprint custom"><code>$1</code></pre>', $data->content );

		$params = [
			'articles_id' => $article_id,
			'acl_users_id' => $this->user->id,
			'user_name' => $this->user->getIdentity()->user_name,
			'email' => $this->user->getIdentity()->email,
			'created' => Nette\Utils\DateTime::from( 'now' ),
			'content' => $data->content,
			'status' => CommentsArticlesRepository::STATUS_PUBLISHED,
		];

		return $this->commentsArticlesRepository->insert( $params );
	}


	public function switchVisibility( $id )
	{
		$row = $this->commentsArticlesRepository->findOneBy( ['id' => $id] );
		return $row->update( [ 'status' => $row->status == CommentsArticlesRepository::STATUS_UNPUBLISHED ? CommentsArticlesRepository::STATUS_PUBLISHED : CommentsArticlesRepository::STATUS_UNPUBLISHED ] );
	}


}