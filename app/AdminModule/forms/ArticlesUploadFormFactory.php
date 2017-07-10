<?php

namespace App\AdminModule\Forms;


use App;
use Nette;
use App\Model\Services\UploadsArticlesService;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class ArticlesUploadFormFactory
{

	/** @var  UploadsArticlesService */
	protected $uploadArticlesService;


	public function __construct( UploadsArticlesService $uAS )
	{
		$this->uploadArticlesService = $uAS;
	}


	public function create( $id )
	{
		$form = new Nette\Application\UI\Form;

		$form->addProtection( 'Vypršal čas na odoslanie formulára. Z bezpečnostných dôvodou bola požiadavka na server zamietnutá.' );

		$form->addMultiUpload( 'files', 'Vložiť súbory' )
			->setRequired( 'Vyberte aspoň jeden súbor.' )
			->addRule( $form::IMAGE, 'Niektorý z vybraných súborov nieje obrázok.' )
			->setAttribute( 'class', ['form-control'] );

		$form->addSubmit( 'submit', 'Uložiť' )
			->setAttribute( 'class', 'btn btn-primary' );

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array( $this, 'formSucceeded' );
		return $form;
	}


	public function formSucceeded( Form $form, $values )
	{
		$presenter = $form->getPresenter();
		$id = (int) $presenter->getParameter( 'id' );

		try
		{
			// $result is array in form ['saved_items' => [], 'errors' => []]
			$result = $this->uploadArticlesService->save_images( $id, $values['files'] );
		}
		catch ( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			$presenter->flashMessage( 'Pri ukladaní obrázkov došlo k chybe.', 'error' );
			return;
		}

		foreach ( $result['errors'] as $error ) $presenter->flashMessage( $error, 'error' );
		foreach ( $result['saved_items'] as $item ) $presenter->flashMessage( 'Súbor ' . $item . ' bol uložený na server.', 'success' );

		$presenter->redirect( 'this', $id );

	}

}
