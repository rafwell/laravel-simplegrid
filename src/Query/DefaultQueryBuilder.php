<?php

namespace Rafwell\Simplegrid\Query;

use Rafwell\Simplegrid\Query\QueryBuilderContract;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Exception;
use Carbon\Carbon;
use Log;

class DefaultQueryBuilder implements QueryBuilderContract
{
	protected $fieldsForSelect = [];
	protected $model;
	protected $searchedValue;
	protected $subqueryMode = false;

	public function __construct(Builder $model)
	{
		$this->model = $model;

		if ($this->model->getQuery()->unions) {
			//when has union must do a sub query
			$db = DB::table(DB::raw("({$this->model->toSql()}) as sub"));

			if ($connection = $this->model->getModel()->getConnectionName()) {
				if (method_exists($connection, 'connection'))
					$db->connection($connection);
			}

			$db->mergeBindings($this->model->getQuery());

			$this->model = $db;

			$this->subqueryMode = true;
		}
	}

	public function setFieldsForSelect(array $fields)
	{
		$this->fieldsForSelect = $fields;

		return $this;
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
				$this->fieldsForSelect[$k] = $v['field'] . ' as ' . $v['alias'];
			} else
				$this->fieldsForSelect[$k] = $v['field'];

			if ($hydrate)
				$fieldsForSelect[$k] = DB::raw($this->fieldsForSelect[$k]);
		}

		return $fieldsForSelect;
	}


	public function paginate($rowsPerPage, $currentPage)
	{
		$this->model->skip(($currentPage - 1) * $rowsPerPage)->take($rowsPerPage);
	}

	public function performSimpleSearch($search)
	{
		$search = trim($search);
		$this->searchedValue = $search;
		$fields = $this->getSimpleSearchConcatenatedFields();
		$this->model->where(DB::raw($fields), 'like', '%' . $search . '%');
	}

	protected function getValueMultipleSearchByIndex(array $search, array $advancedSearchFields, int $i)
	{
		$res = [];

		foreach ($search[$i] as $field => $value) {

			if ($advancedSearchFields[$field]['multiple']) {
				$prepare = array_map(function ($item) {
					return ['field' => key($item), 'value' => $item[key($item)]];
				}, $search);


				$collect = collect($prepare)->where('field', $field)->pluck('value');

				foreach ($collect as $item) {
					$res[] = $item;
				}
			} else {
				$res[] = [$field => $value];
			}
		}

		return $res;
	}

	public function performAdvancedSearch(array $search, array $advancedSearchFields, array $advancedSearchOptions)
	{

		for ($i = 0; $i < count($search); $i++) {

			foreach ($search[$i] as $field => $value) {



				if (!is_callable($advancedSearchFields[$field]['where']))
					$fieldSearched = $this->fieldsForSelect[$field]['field'];
				else {
					$fieldSearched = '';
				}


				//dd($this->searchedValue);

				if ($advancedSearchFields[$field]['multiple']) {
					$value = $this->getValueMultipleSearchByIndex($search, $advancedSearchFields, $i);

					$this->searchedValue[$field] = $value;

					if (count($value) > 0 && $advancedSearchFields[$field]['where'] === false) {
						$this->model->whereIn(DB::raw('(' . $fieldSearched . ')'), $value);
					}
					$valueProcessed = $value;
				} else
				if (is_string($value)) {
					$value = trim($value);
					$this->searchedValue[$field] = $value;

					if ($value !== '' && $advancedSearchFields[$field]['where'] === false) {
						if (is_string($advancedSearchFields[$field]) || $advancedSearchFields[$field]['type'] == 'text')
							$this->model->where(DB::raw('(' . $fieldSearched . ')'), 'like', '%' . $value . '%');
						else
							$this->model->where(DB::raw('(' . $fieldSearched . ')'), $value);
					}
					$valueProcessed = $value;
				} else {
					if (isset($value['from']) && $value['from'] !== '')
						$valueAux = $value['from'];
					else
					if (isset($value['to']) && $value['to'] !== '')
						$valueAux = $value['to'];
					else
						$valueAux = '';

					$valueProcessed = '';

					switch ($advancedSearchFields[$field]['type']) {
						case 'date':
						case 'datetime':
							if ($valueAux) {
								$type = $advancedSearchFields[$field]['type'];
								$inputFormat = $advancedSearchOptions['formats'][$type]['input'][1];

								$processFormat = $advancedSearchOptions['formats'][$type]['processTo'][1];

								$valueProcessed = $valueAux;

								if ($inputFormat != $processFormat)
									$valueProcessed = Carbon::createFromFormat($inputFormat, $valueAux)->format($processFormat);
							}
							break;
						case 'integer':
							$valueProcessed = (int) $valueAux;
							break;
						case 'decimal':
							$valueProcessed = (float) $valueAux;
							break;
						default:

							$valueProcessed = $valueAux;
							break;
					}

					$valueProcessed = trim($valueProcessed);

					if (isset($value['from']) && $value['from'] !== '') {
						$this->searchedValue[$field . '_from'] = $valueAux;
						if ($advancedSearchFields[$field]['where'] === false)
							$this->model->where(DB::raw('(' . $fieldSearched . ')'), '>=', $valueProcessed);
					}

					if (isset($value['to']) && $value['to'] !== '') {
						$this->searchedValue[$field . '_to'] = $valueAux;
						if ($advancedSearchFields[$field]['where'] === false)
							$this->model->where(DB::raw('(' . $fieldSearched . ')'), '<=', $valueProcessed);
					}
				}

				if (is_callable($advancedSearchFields[$field]['where'])) {
					//the user will make the where
					call_user_func($advancedSearchFields[$field]['where'], $this->model, $valueProcessed);
				}
			}
		}
	}

	public function getSearchedValue()
	{
		return $this->searchedValue;
	}

	public function getModelQuery($model)
	{
		return $this->subqueryMode ? $model : $model->getQuery();
	}

	public function sort($sortedField, $direction)
	{
		$this->getModelQuery($this->model)->orders = null;
		$field = $this->getFieldRaw($sortedField);

		$this->model->orderBy($field, $direction);
	}

	private function getFieldRaw($field)
	{
		if (array_key_exists($field, $this->fieldsForSelect) === false)
			throw new Exception('Field "' . $field . '" not exists in select.');
		else {
			$field = strpos($this->fieldsForSelect[$field]['field'], ' ') === false ? $this->fieldsForSelect[$field]['field'] : '(' . $this->fieldsForSelect[$field]['field'] . ')';
			return DB::raw($field);
		}
	}

	public function processUsedFields($fields, $actionFields, $advancedSearchFields)
	{
		$usedFields = $this->processFields($fields);

		$processedActionFields = $this->processActionFields($actionFields);

		foreach ($processedActionFields as $key => $processed) {
			if (!isset($usedFields[$key])) {
				$usedFields[$key] = $processed;
			}
		}

		$this->setFieldsForSelect($usedFields);

		return $this;
	}

	private function processFields($fields)
	{
		return $fields;
	}

	private function processActionFields(array $actionFields = [])
	{
		$fields = [];

		return $actionFields;
	}

	public function getSimpleSearchConcatenatedFields()
	{
		$where = '';

		foreach ($this->fieldsForSelect as $field) {
			$where .= ",COALESCE(({$field['field']}), '')";
		}

		if ($where)
			$where = 'CONCAT(' . substr($where, 1) . ')';

		return $where;
	}

	public function getTotalRows()
	{
		$countModel = clone ($this->model);
		$this->getModelQuery($countModel);
		$this->getModelQuery($countModel)->orders = null;

		if ($this->getModelQuery($countModel)->groups) {
			//when has group by need count over an sub query
			$db = DB::table(DB::raw("({$countModel->selectRaw('1')->toSql()}) as sub"));

			if ($connection = $countModel->getModel()->getConnectionName())
				$db->connection($connection);

			$db->mergeBindings($this->getModelQuery($countModel));

			return $db->count();
		} else {
			return $countModel->count();
		}
	}

	public function buildQueryForGet()
	{
		return $this->model->select($this->getFieldsForSelect());
	}

	public function performQueryAndGetRows()
	{
		$data = $this->buildQueryForGet()->get()->toArray();
		if ($this->subqueryMode)
			$data = collect($data)->map(function ($x) {
				return (array) $x;
			})->toArray();
		return $data;
	}
}
