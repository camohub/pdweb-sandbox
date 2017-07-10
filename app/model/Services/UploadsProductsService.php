<?php

namespace App\Model\Services;


use App;
use Nette;
use App\Model\Repositories\UploadsProductsRepository;
use App\Model\Repositories\ModulesRepository;


class UploadsProductsService
{

	/** @var  UploadsProductsRepository */
	public $uploadsProductsRepository;

	/** @var  ModulesRepository */
	public $modulesRepository;

	/** @var  int */
	protected $blog_module_id;

	/** @var  int */
	protected $eshop_module_id;

	/** @var  string */
	protected $www_dir;


	public function __construct( $www_dir, UploadsProductsRepository $uPR, ModulesRepository $mR )
	{
		$this->uploadsProductsRepository = $uPR;
		$this->modulesRepository = $mR;
		$this->www_dir = $www_dir;
	}


	public function save_blog_images( $files )
	{
		$module = $this->modulesRepository->findOneBy( [ 'name =' => 'blog' ] );
		$path = $this->wwwDir . '/uploads/articles';

		if ( ! is_dir( $path ) && ! mkdir( $path, 0777 ) )
		{
			// Dir blog does not exist and can not be created
			throw new App\Exceptions\CreateDirectoryException( 'Nepodarilo sa vytvoriť adresár pre obrázky. Kontaktujte prosím administrátora.' );
		}
		if ( ! is_dir( $path . '/thumbnails' ) && ! mkdir( $path . '/thumbnails', 0777 ) )
		{
			throw new App\Exceptions\CreateDirectoryException( 'Nepodarilo sa vytvoriť adresár pre obrázky. Kontaktujte prosím administrátora.' );
		}
		if ( ! is_dir( $path . '/mediums' ) && ! mkdir( $path . '/mediums', 0777 ) )
		{
			throw new App\Exceptions\CreateDirectoryException( 'Nepodarilo sa vytvoriť adresár pre obrázky. Kontaktujte prosím administrátora.' );
		}

		$result = [ 'errors' => [ ], 'saved_items' => [ ] ];
		foreach ( $files as $file )
		{
			if ( $file->isOk() )
			{
				$name = $file->getName();
				$sName = $file->getSanitizedName();
				$tmpName = $file->getTemporaryFile();

				$spl = new \SplFileInfo( $sName );
				$sName = $spl->getBasename( '.' . $spl->getExtension() ) . '-' . microtime( TRUE ) . '.' . $spl->getExtension();

				$this->em->beginTransaction();

				try
				{
					try
					{
						$file = new Entity\Image();
						$file->create( [
							'name'   => $sName,
							'module' => $module,
						] );
						$this->em->persist( $file );
						$this->em->flush( $file );
					}
					catch ( UniqueConstraintViolationException $e )
					{
						$result['errors'][] = 'Súbor s názvom ' . $name . ' už existuje.';
						$this->em->rollback();
						$this->reopenEm();
						// If exception occurs $module is detached and needs to be merged.
						$module = $this->em->merge( $module );  // Is necessary to write $module = merge( $module ).
						continue;
					}

					$img = Image::fromFile( $tmpName );
					$x = $img->width;
					$y = $img->height;

					if ( $x > 1200 || $y > 1000 )
					{
						$img->resize( 1200, 1000 );  // Keeps ratio => one of the sides can be shorter, but none will be longer
					}
					$img->save( $path . '/' . $sName );

					if ( $x > 400 )
					{
						$img->resize( 400, NULL );  // Width will be 400px and height keeps ratio
					}
					$img->save( $path . '/mediums/' . $sName );

					if ( $x > 150 )
					{
						$img->resize( 150, NULL );  // Width will be 150px and height keeps ratio
					}
					$img->save( $path . '/thumbnails/' . $sName );

					$result['saved_items'][] = $name;

					$this->em->commit();
				}
				catch ( \Exception $e )
				{
					$this->em->rollback();
					Debugger::log( $e->getMessage(), 'ERROR' );
					$this->reopenEm();
					$module = $this->em->merge( $module );  // Is necessary to write $module = merge( $module ).
					@$this->unlink( $sName );  // If something is saved, delete it.
					$result['errors'][] = 'Pri ukladaní súboru ' . $name . ' došlo k chybe. Súbor nebol uložený.';
				}
			}
			else
			{
				$result['errors'][] = 'Pri ukladaní súboru došlo k chybe.';
			}
		}

		return $result;
	}


}
