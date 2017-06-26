<?php

namespace App\Model\Repositories;


use Nette;


abstract class Repository extends Nette\Object
{

	const TBL_NAME = '';

	/** @var Nette\Database\Context */
	protected $database;


	public function __construct( Nette\Database\Context $db )
	{
		$this->database = $db;
	}


	protected function getTable( $name = NULL )
	{
		return $name ? $this->database->table( $name ) : $this->database->table( static::TBL_NAME );  // self:: does not rewrite const value in child class.
	}


	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function findAll()
	{
		return $this->getTable();
	}


	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function findBy( array $by )
	{
		return $this->getTable()->where( $by );
	}


	/**
	 * @return Nette\Database\Table\Selection
	 */
	public function findOneBy( array $by )
	{
		return $this->getTable()->where( $by )->limit( 1 )->fetch();
	}


	/**
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function findById( $id )
	{
		return $this->getTable()->get( $id );
	}


	/**
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function add( $values )
	{
		return $this->getTable()->insert( $values );
	}


	/**
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function update( $id, $values )
	{
		return $this->getTable()->get( $id )->update( $values );
	}


	/**
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function remove( $id )
	{
		return $this->getTable()->get( $id )->delete();
	}


	/**
	 * Returns array for rendering Nette\Forms\Controls\SelectBox
	 *
	 * @param $value
	 * @param $label
	 * @return array
	 */
	public function fillSelect( $value, $label )
	{
		return $this->getTable()->order( $label )->fetchPairs( $value, $label );
	}
}
