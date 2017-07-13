<?php

namespace App\Model\Repositories;


use Nette;


class ArticlesRepository extends Repository
{

	const TBL_NAME = 'articles';
	const STATUS_VIRTUAL = 1;
	const STATUS_PUBLISHED = 2;
	const STATUS_UNPUBLISHED = 3;
	const STATUS_DELETED = 4;


	public function findBy( array $by )
	{
		return parent::findBy( $by )->select('
			articles.*, 
			:articles_langs.meta_desc, 
			:articles_langs.title, 
			:articles_langs.slug, 
			:articles_langs.perex, 
			:articles_langs.content
		');
	}


	public function findOneBy( array $by)
	{
		return $this->findBy( $by )->limit( 1 )->fetch();
	}

}
