<?php


namespace App\Presenters;


use Nette;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var \App\FrontModule\Components\Menu\IFrontMenuControlFactory @inject */
	public $frontMenuFactory;

	/** @var \App\AdminModule\Components\Menu\IAdminMenuControlFactory @inject */
	public $adminMenuFactory;

	/** @var \WebLoader\Nette\LoaderFactory @inject */
	public $webLoader;


	/**
	 * @desc Sets referrer url as string or '' to unique $id section for every page
	 * @param string $id
	 */
	protected function setReferer( $id = '' )
	{
		if ( ! $id )
		{
			return;
		}

		$url = '';

		if ( $referer = $this->getHttpRequest()->getReferer() )
		{
			$url = $referer->getScheme() . '://' . $referer->getHost() . '/' . $referer->getPath();

			if ( $qsArr = $referer->getQueryParameters() ) // returns array
			{
				foreach ( $qsArr as $key => $val )
				{
					if ( $key == self::FLASH_KEY )
					{
						continue;
					}

					$url .= isset( $i ) ? '&' . $key . '=' . $val : '?' . $key . '=' . $val;
					$i = 1;
				}
			}
		}

		$this->getSession( $id )->url = $url;
	}


	/**
	 * @desc Returns referrer url or false
	 * @param string $id
	 * @return bool|mixed|string
	 */
	protected function getReferer( $id = '' )
	{
		$refSess = $this->getSession( $id );
		$url = $refSess->url;

		if ( ! $id || ! $url )
		{
			return FALSE;
		}

		$url .= ( parse_url( $url, PHP_URL_QUERY ) ? '&' : '?' );
		$url .= self::FLASH_KEY . '=' . $this->getParameter( self::FLASH_KEY );

		unset( $refSess->url );

		return $url;
	}


	/** @return \DK\Menu\UI\Control */
	protected function createComponentFrontMenu()
	{
		return $this->frontMenuFactory->create();
	}


	/** @return \DK\Menu\UI\Control */
	protected function createComponentAdminMenu()
	{
		return $this->adminMenuFactory->create();
	}


	/** @return CssLoader */
	protected function createComponentFrontCss()
	{
		return $this->webLoader->createCssLoader( 'front' );
	}


	/** @return CssLoader */
	protected function createComponentAdminCss()
	{
		return $this->webLoader->createCssLoader( 'admin' );
	}


	/** @return JavaScriptLoader */
	protected function createComponentFrontJs()
	{
		return $this->webLoader->createJavaScriptLoader( 'front' );
	}


	/** @return JavaScriptLoader */
	protected function createComponentAdminJs()
	{
		return $this->webLoader->createJavaScriptLoader( 'admin' );
	}

}
