<?php


namespace App\Model\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 * @ORM\Table(name="categories_articles",
 * 		indexes={
 * 			@ORM\Index(name="priority_idx", columns={"priority"})
 * 		},
 * 		options={"collate"="utf8_slovak_ci"}
 * )
 */
class CategoryArticle
{

	const CATEGORY_NEWS = 1;

	//use \Kdyby\Doctrine\Entities\MagicAccessors;
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;


	/**
	 * @ORM\OneToMany(targetEntity="CategoryArticleLang", mappedBy="category", cascade={"persist", "remove"})
	 */
	protected $langs;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $parent_id;

	/**
	 * Needs to have parent_id param to be defined.
	 * @ORM\ManyToOne(targetEntity="CategoryArticle", inversedBy="children", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent;

	/**
	 * Now is not used to generating menu, but still useful when deleting child entities.
	 * @ORM\OneToMany(targetEntity="CategoryArticle", mappedBy="parent")
	 */
	protected $children;

	/** @ORM\Column(type="string", length=25) */
	protected $url;

	/** @ORM\Column(type="smallint", options={"unsigned"=true}) */
	protected $priority;

	/**
	 * @ORM\ManyToOne(targetEntity="Status", inversedBy="categories_articles")
	 * @ORM\JoinColumn(name="statuses_id", nullable=false, referencedColumnName="id", onDelete="RESTRICT")
	 */
	protected $status;

	/** @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true, "comment"="If app == 1 item can't be deleted cause it is native part of application."}) */
	protected $app;

	/**
	 * @ORM\ManyToMany(targetEntity="Article", mappedBy="categories")
	 */
	protected $articles;


	public function __construct()
	{
		$this->articles = new ArrayCollection();
		$this->children = new ArrayCollection();
		$this->langs = new ArrayCollection();
		$this->priority = 1;
		$this->app = 0;
	}


	public function getUrl()
	{
		return $this->url;
	}


	public function setUrl( $url )
	{
		return $this->url = $url;
	}


	public function getPriority()
	{
		return $this->priority;
	}


	public function setPriority( $to )
	{
		$this->priority = (int) $to;
	}


	public function getChildren()
	{
		return $this->children;
	}


	public function getParent()
	{
		return $this->parent;
	}


	public function getArticles()
	{
		return $this->articles;
	}


	public function getLangs()
	{
		return $this->langs;
	}


	public function getLang( $code )
	{
		return $this->langs->filter( function ( $item ) use ( $code ) {
			return $item->getCode() == $code;
		})->first();
	}


	public function getDefaultLang()
	{
		return $this->getLang( 'sk' );
	}


	public function addLang( CategoryArticleLang $lang )
	{
		return $this->langs->add( $lang );
	}


	public function getApp()
	{
		return $this->app;
	}


	public function setApp( $app )
	{
		return $this->app = $app;
	}


	public function getStatus()
	{
		return $this->status;
	}


	public function setStatus( Status $status )
	{
		return $this->status = $status;
	}


	public function setParentId( $id )
	{
		$this->parent_id = $id;  // Do not use (int) because of NULL.
	}


	public function setParent( CategoryArticle $parent = NULL )
	{
		$this->parent = $parent;  // Do not use (int) because of NULL.
	}


}