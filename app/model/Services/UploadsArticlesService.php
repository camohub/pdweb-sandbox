<?php

namespace App\Model\Services;


use App;
use Nette;
use App\Model\Repositories\UploadsArticlesRepository;
use App\Model\Repositories\ModulesRepository;
use Tracy\Debugger;


class UploadsArticlesService
{

	/** @var  UploadsArticlesRepository */
	public $uploadsArticlesRepository;

	/** @var  ModulesRepository */
	public $modulesRepository;

	/** @var  int */
	protected $blog_module_id;

	/** @var  int */
	protected $eshop_module_id;

	/** @var string */
	protected $www_dir;


	public function __construct( $www_dir, UploadsArticlesRepository $uAR, ModulesRepository $mR )
	{
		$this->uploadsArticlesRepository = $uAR;
		$this->modulesRepository = $mR;
		$this->www_dir = $www_dir;
	}


	public function save_images( $id, $files )
	{
		$path = $this->www_dir . '/uploads/articles/' . $id;

		try
		{
			Nette\Utils\FileSystem::createDir( $path );
			Nette\Utils\FileSystem::createDir( $path . '/thumbnails' );
			Nette\Utils\FileSystem::createDir( $path . '/mediums' );
		}
		catch( \Exception $e )
		{
			Debugger::log( $e->getMessage() . ' @ in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
			throw $e;
		}

		$result = ['errors' => [], 'saved_items' => []];
		foreach ( $files as $file )
		{
			if ( $file->isOk() )
			{
				$name = $file->getName();
				$sName = $file->getSanitizedName();
				$tmpName = $file->getTemporaryFile();

				$spl = new \SplFileInfo( $sName );
				$sName = $spl->getBasename( '.' . $spl->getExtension() ) . '-' . microtime( TRUE ) . '.' . $spl->getExtension();

				$this->uploadsArticlesRepository->getDatabase()->beginTransaction();

				try
				{
					try
					{
						$this->uploadsArticlesRepository->insert( [
							'articles_id' => $id,
							'name'   => $sName,
						] );
					}
					catch ( \PDOException $e )
					{
						// This catch ONLY checks duplicate entry to fields with UNIQUE KEY
						$this->uploadsArticlesRepository->getDatabase()->rollBack();

						$info = $e->errorInfo;
						// mysql==1062  sqlite==19  postgresql==23505
						if ( $info[0] == 23000 && $info[1] == 1062 )
						{
							$result['errors'][] = 'Súbor s názvom ' . $name . ' už v databáze existuje.';
						}
						else
						{
							throw $e;
						}

						continue;  // Foreach continue
					}
					catch ( \Exception $e )
					{
						$this->uploadsArticlesRepository->getDatabase()->rollBack();
						Debugger::log( $e->getMessage() . ' @in file ' . __FILE__ . ' on line ' . __LINE__, 'error' );
						throw $e;
					}

					$img = Nette\Utils\Image::fromFile( $tmpName );
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

					$this->uploadsArticlesRepository->getDatabase()->commit();
				}
				catch ( \Exception $e )
				{
					$this->uploadsArticlesRepository->getDatabase()->rollback();
					Debugger::log( $e->getMessage(), 'error' );
					@$this->unlink( $path, $sName );  // If something is saved, delete it.
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

///// PROTECTED /////////////////////////////////////////////////////////////////////////////////////

	protected function unlink( $path, $name )
	{
		@unlink( $path . '/' . $name );
		@unlink( $path . '/mediums/' . $name );
		@unlink( $path . '/thumbnails/' . $name );
	}

}
