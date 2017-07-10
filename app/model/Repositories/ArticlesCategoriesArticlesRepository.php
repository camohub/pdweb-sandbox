<?php

namespace App\Model\Repositories;


use Nette;


class ArticlesCategoriesArticlesRepository extends Repository
{

	/** Join table between articles and categories_articles */
	const TBL_NAME = 'articles_categories_articles';

}
