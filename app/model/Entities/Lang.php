<?php


namespace App\Model\Entity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Tracy\Debugger;


/**
 * @ORM\Entity
 * @ORM\Table(
 * 		name="langs",
 * 		options={"collate"="utf8_slovak_ci"}
 * )
 */
class Lang
{

	//use \Kdyby\Doctrine\Entities\MagicAccessors;
	use \Kdyby\Doctrine\Entities\Attributes\Identifier;


	const SK = 1;  // Values of this constants ensures migration
	const EN = 2;

	/**
	 * @ORM\Column(type="string", length=30)
	 */
	protected $code;


	/**
	 * @ORM\OneToMany(targetEntity="ArticleLang", mappedBy="lang", cascade={"persist", "remove"})
	 */
	protected $articles_langs;


	/**
	 * @ORM\OneToMany(targetEntity="ArticleLang", mappedBy="lang", cascade={"persist", "remove"})
	 */
	protected $categories_langs;


	public function __construct( $params = [ ] )
	{
		$this->articlesLangs = new ArrayCollection();

	}


	public function getArticles()
	{
		return $this->articles_langs;
	}


	public function getCategories()
	{
		return $this->articles_categories;
	}


	public function getCode()
	{
		return $this->code;
	}


}