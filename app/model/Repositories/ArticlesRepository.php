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

}
