<?php

namespace App\Model\Repositories;


use Nette;
use Kdyby;
use App\Exceptions\InvalidArgumentException;
use Tracy\Debugger;


class CommentsArticlesRepository extends Repository
{

	CONST TBL_NAME = 'comments_articles';
	CONST STATUS_UNPUBLISHED = 0;
	CONST STATUS_PUBLISHED = 1;

}