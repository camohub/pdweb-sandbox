<?php

namespace App\Model\Repositories;


use Nette;
use Nette\Caching\Cache;


class CategoriesProductsRepository extends Repository
{

	const TBL_NAME = 'categories_products';
	const CACHE = 'products_categories_cache';
	const CACHE_HTML_TAG = 'products_categories_html';

	/** @var  @var Nette\Caching\IStorage */
	protected $storage;

	/** @var  @var Nette\Caching\Cache */
	protected $cache;


	public function __construct( Nette\Database\Context $db, Nette\Caching\IStorage $s )
	{
		parent::__construct($db);

		$this->storage = $s;
		$this->cache = new Cache( $this->storage, self::CACHE );
	}


	/**
	 * @desc Cleans the menu cache.
	 */
	public function cleanHtmlCache()
	{
		// Now is_in_cache from menu.latte is not used.
		// Because of latte cache is invalidated if latte code was changed.
		// Then is necessary to invalidate it manually. We do not want it.
		$this->cache->clean( [ Cache::TAGS => [ self::CACHE_HTML_TAG/*, 'is_in_cache'*/ ] ] );
	}

}
