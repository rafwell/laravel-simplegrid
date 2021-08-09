<?php

namespace Rafwell\Simplegrid\Query;

use Rafwell\Simplegrid\Query\QueryBuilderContract;
use Rafwell\Simplegrid\Query\DefaultQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Exception;
use Carbon\Carbon;
use Log;

class SqlServerQueryBuilder extends DefaultQueryBuilder implements QueryBuilderContract
{
	protected $fieldsForSelect = [];
	protected $model;
	protected $searchedValue;

	public function __construct(Builder $model)
	{
		$this->model = $model;
	}

	public function getFieldsForSelect($hydrate = true, $addAlias = true)
	{
		$fieldsForSelect = [];

		foreach ($this->fieldsForSelect as $k => $v) {
			if (!is_array($v)) {
				Log::debug($this->fieldsForSelect);
				throw new Exception('An array was expected.');
			}

			if (strpos($v['field'], ' ') !== false) {
				$v['field'] = '(' . $v['field'] . ')';
			}
			if ($v['field'] <> $v['alias']) {
				$fieldsForSelect[$k] = $v['field'] . ' as ' . $v['alias'];
			} else
				$fieldsForSelect[$k] = $v['field'];

			if ($hydrate)
				$fieldsForSelect[$k] = DB::raw($fieldsForSelect[$k]);
		}

		return $fieldsForSelect;
	}

	public function getSimpleSearchConcatenatedFields()
	{
		$where = '';

		foreach ($this->fieldsForSelect as $field) {
			$where .= "+COALESCE(CAST({$field['field']} AS NVARCHAR(MAX)), '')";
		}

		if ($where)
			$where = substr($where, 1);

		return $where;
	}
}
