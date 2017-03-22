<?php
namespace Rafwell\Simplegrid\Query;

use Rafwell\Simplegrid\Query\QueryBuilderContract;
use Rafwell\Simplegrid\Query\DefaultQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Exception;
use Carbon\Carbon;

class OdbcQueryBuilder extends DefaultQueryBuilder implements QueryBuilderContract {
	protected $fieldsForSelect = [];
	protected $model;
	protected $searchedValue;

	public function __construct(Builder $model){
		$this->model = $model;
	}	

	public function getFieldsForSelect($hydrate = true, $addAlias = true){
		$fieldsForSelect = [];		

		foreach($this->fieldsForSelect as $k=>$v){
			if(!is_array($v)) dd($this->fieldsForSelect);

			if(strpos($v['field'], ' ')!==false){
				$v['field'] = '('.$v['field'].')';
			}
			if($v['field'] <> $v['alias']){				
				$this->fieldsForSelect[$k] =  $v['field'].' as ['.$v['alias'].']';				
			}
			else 
				$this->fieldsForSelect[$k] = $v['field'];

			if($hydrate)
				$fieldsForSelect[$k] = DB::raw($this->fieldsForSelect[$k]);
		}

		return $fieldsForSelect;

	}

	public function getSimpleSearchConcatenatedFields(){
		$where = '';
		
		foreach($this->fieldsForSelect as $field){				
			$where.='+'.$field['field'];
		}

		if($where)
			$where = substr($where, 1);
		
		return $where;
	}	
}