<?php


namespace App\Model\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\Strings;


/**
 * @ORM\Entity
 * @ORM\Table(name="categories_articles_langs",
 * 		indexes={
 * 			@ORM\Index(name="categories_articles_langs_slug_idx", columns={"slug"})
 * 		},
 * 		uniqueConstraints={
 * 			@ORM\Uniqueconstraint(name="categories_langs_slug_lang_id_uidx", columns={"slug", "lang_id"})
 * 		},
 * 		options={"collate"="utf8_slovak_ci"}
 * )
 */
class CategoryArticleLang
{

	//use \Kdyby\Doctrine\Entities\MagicAccessors;
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;


	/**
	 * @ORM\ManyToOne(targetEntity="CategoryArticle", inversedBy="langs", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="category_article_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	protected $category;

	/**
	 * @ORM\ManyToOne(targetEntity="Lang", inversedBy="categories_langs")
	 * @ORM\JoinColumn(name="lang_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	protected $lang;

	/** @ORM\Column(type="string", length=150) */
	protected $title;

	/** @var  @ORM\Column(type="string", length=150) */
	protected $slug;


	public function __construct()
	{

	}


	public function getCode()
	{
		return $this->lang->getCode();
	}


	public function getTitle()
	{
		return $this->title;
	}


	public function setTitle( $title )
	{
		$this->title = $title;
		return $this->slug = Strings::webalize( $title );
	}


	public function getSlug()
	{
		return $this->slug;
	}


	public function getCategory()
	{
		return $this->category;
	}


	public function setCategory( CategoryArticle $category )
	{
		$this->category = $category;
	}


	public function setLang( Lang $lang )
	{
		$this->lang = $lang;
	}



}