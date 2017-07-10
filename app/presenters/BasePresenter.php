<?php


namespace App\Presenters;


use App;
use Nette;
use NasExt;
use WebLoader\Nette\CssLoader;
use WebLoader\Nette\JavaScriptLoader;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var \App\FrontModule\Components\ICamoMenuControlFactory @inject */
	public $camoMenuFactory;

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
	public function setReferer( $id = '' )
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
	public function getReferer( $id = '' )
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


	/**
	 * @desc This method sets section id for javascript which opens/closes menu items.
	 */
	public function setCategoryId( $id )
	{
		$this['camoMenu']->setCategory( $id );
	}


	/**
	 * @param $name
	 * @return NasExt\Controls\VisualPaginator
	 */
	protected function createComponentVp( $name )
	{
		$control = new NasExt\Controls\VisualPaginator( $this, $name );
		// enable ajax request, default is false
		/*$control->setAjaxRequest();

		$that = $this;
		$control->onShowPage[] = function ($component, $page) use ($that) {
		if($that->isAjax()){
		$that->invalidateControl();
		}
		};*/
		return $control;
	}

//// HELPERS //////////////////////////////////////////////////////////////////////////////



/////////helpers//////////////////////////////////////////////////////

	/**
	 * @desc Helpers
	 * @desc 1. To translates of moths and days names.
	 * @desc 2. Adds prettyprint class to the pre tags.
	 * @param null $class
	 * @return Nette\Application\UI\ITemplate
	 */
	protected function createTemplate( $class = NULL )
	{
		$template = parent::createTemplate( $class );
		$template->addFilter( 'datum', function ( $s, $lang = 'sk' )
		{
			$needles = array( 'Monday', 'Tuesday', 'Wensday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Mon', 'Tue', 'Wen', 'Thu', 'Fri', 'Sat', 'Sun' );
			$sk = array( 'pondelok', 'utorok', 'streda', 'štvrtok', 'piatok', 'sobota', 'nedeľa', 'január', 'február', 'marec', 'apríl', 'máj', 'jún', 'júl', 'august', 'september', 'október', 'november', 'december', 'jan.', 'feb.', 'mar.', 'apr.', 'máj', 'jún', 'júl', 'aug.', 'sep.', 'okt.', 'nov.', 'dec.', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne' );

			return str_replace( $needles, $$lang, $s );
		}
		);

		return $template;
	}

///// COMPONENTS ////////////////////////////////////////////////////////////////////////////

	/** @return App\FrontModule\Components\CamoMenu */
	protected function createComponentCamoMenu()
	{
		return $this->camoMenuFactory->create();
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
