<?php


namespace App\Model\Entity;


use Kdyby;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Tracy\Debugger;


/**
 * @ORM\Entity
 * @ORM\Table(name="articles_langs",
 * 		uniqueConstraints={
 * 			@ORM\uniqueconstraint(name="articles_langs_slug_lang_id_uidx", columns={"slug", "lang_id"})
 * 		},
 * 		options={"collate"="utf8_slovak_ci"}
 * )
 */
class ArticleLang extends Nette\Object
{

	//use Kdyby\Doctrine\Entities\MagicAccessors;
	use Kdyby\Doctrine\Entities\Attributes\Identifier;


	/**
	 * @ORM\ManyToOne(targetEntity="Article", inversedBy="langs", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private $article;

	/**
	 * @ORM\ManyToOne(targetEntity="Lang", inversedBy="articles_langs")
	 * @ORM\JoinColumn(name="lang_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
	 */
	private $lang;

	/** @ORM\Column(type="string", length=255) */
	private $meta_desc;

	/** @ORM\Column(type="string", length=255) */
	private $title;

	/** @ORM\Column(type="string", length=255) */
	private $slug;

	/** @ORM\Column(type="text") */
	private $perex;

	/** @ORM\Column(type="text") */
	private $content;


	public function __construct()
	{

	}


	public function update( $params )
	{
		if ( isset( $params['title'] ) )
		{
			$this->title = $params['title'];
			$this->slug = Nette\Utils\Strings::webalize( $params['title'] );
		}
		if ( isset( $params['meta_desc'] ) )
		{
			$this->meta_desc = $params['meta_desc'];
		}
		if ( isset( $params['perex'] ) )
		{
			$this->perex = $params['perex'];
		}
		if ( isset( $params['content'] ) )
		{
			$this->content = $params['content'];
		}
	}


	public function getArray()
	{
		$arr = [
			'meta_desc' => $this->meta_desc,
			'title'     => $this->title,
			'perex'     => $this->perex,
			'content'   => $this->content,
			'status'    => $this->status,
		];

		$arr['categories'] = [ ];
		foreach ( $this->categories as $category )
		{
			if ( $category->getId() == 7 )
			{
				continue;
			}
			$arr['categories'][] = $category->getId();
		}

		return $arr;
	}


	public function getArticle()
	{
		return $this->article;
	}


	public function setArticle( Article $article )
	{
		return $this->article = $article;
	}


	public function getLang()
	{
		return $this->lang;
	}


	public function setLang( Lang $lang )
	{
		return $this->lang = $lang;
	}


	public function getCode()
	{
		return $this->lang->getCode();
	}


	public function getMetaDesc()
	{
		return $this->meta_desc;
	}


	public function setMetaDesc( $meta_desc )
	{
		return $this->meta_desc = $meta_desc;
	}


	public function getTitle()
	{
		return $this->title;
	}


	public function setTitle( $title )
	{
		$this->title = $title;
		return $this->slug = Nette\Utils\Strings::webalize( $title );
	}


	public function getSlug()
	{
		return $this->slug;
	}


	public function getPerex()
	{
		return $this->perex;
	}


	public function setPerex( $perex )
	{
		return $this->perex = $perex;
	}


	public function getContent()
	{
		return $this->content;
	}


	public function setContent( $content )
	{
		return $this->content = $content;
	}


}