<?php
namespace Rafwell\Simplegrid\Query;
use Illuminate\Database\Eloquent\Builder;
use Rafwell\Simplegrid\Query\DefaultQueryBuilder;
use Rafwell\Simplegrid\Query\OdbcQueryBuilder;

class QueryBuilder{
	private $model;
	private $driverName;
	private $builder;

	public function __construct(Builder $model){
		$this->model = $model;

		switch($this->getDriverName()){			
			case 'odbc':
				$this->builder = new OdbcQueryBuilder($this->model);
			break;
			default:						
				$this->builder = new DefaultQueryBuilder($this->model);
			break;				
		}
	}

	private function getDriverName(){
		$this->driverName = strtolower($this->model->getConnection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));
	}

	public function getBuilder(){
		return $this->builder;
	}
	
}