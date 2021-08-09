<?php

namespace Rafwell\Simplegrid\Query;

use Illuminate\Database\Eloquent\Builder;

interface QueryBuilderContract
{
	public function __construct(Builder $model);

	public function getFieldsForSelect($hydrate = true, $addAlias = true);

	public function paginate($rowsPerPage, $currentPage);

	public function performSimpleSearch($search);

	public function getSimpleSearchConcatenatedFields();

	public function performAdvancedSearch(array $search, array $advancedSearchFields, array $advancedSearchOptions);

	public function sort($field, $direction);
}
