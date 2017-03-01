<?php
namespace Rafwell\Simplegrid;
use Rafwell\Grid\GridController;
use Illuminate\Http\Request;
use DB;
use Rafwell\Grid\Helpers;
use Carbon\Carbon;
use View;

class Grid{
	private $view;
	public $query;
	public $id;
	public $fields;
	public $extraFields;
	public $selectFields = [];	
	public $actions;
	public $currentPage = 1;	
	public $totalPages;
	public $totalRows;
	public $searchedValue;
	public $searchFields;	
	public $fieldsWhereSearch;
	public $checkbox = ['show'=>false, 'field'=>false];
	public $bulkActions;
	public $advancedSearch = false;
	public $advancedSearchOpened = false;
	public $advancedSearchFields = [];
	public $rowsPerPage = [10,20,30,50,100,200];
	public $currentRowsPerPage = 10;
	public $processLineClosure;
	public $export = true;
	public $showTrashedLines = false;
	public $defaultOrder = []; //['field', 'direction']
	private $driverName = '';
	private $allowExport = true;

	function __construct($query, $id){
		$this->query = $query;		
		$this->id = $id;				
		$this->Request = Request::capture();
		$this->driverName = strtolower($this->query->getConnection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME));
		return $this;
	}

	public function fields($fields){
		foreach($fields as $k=>$v){
			if(is_string($v)){
				$v = [
					'label'=>$v,
					'field'=>$k					
				];
			}
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$v['alias'] = substr($k, $strrpos+1);				
			}else{
				$v['alias'] = $k;
			}

			$fields[$k] = $v;
		}

		foreach($fields as $k=>$v){
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$k = substr($k, $strrpos+1);
			}
			$this->selectFields[$k] = $k;
		}

		$this->fields = $fields;

		return $this;
	}	

	public function actionFields($fields){
		$this->actionFields = $fields;

		foreach($fields as $k){
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$k = substr($k, $strrpos+1);
			}
			$this->selectFields[$k] = $k;
		}

		return $this;
	}

	public function action($title, $url, $options = []){
		$defaultOptions = [
			'title'=>$title,
			'url'=>$url,
			'icon'=>false,
			'onlyIcon'=>false,
			'method'=>'GET',
			'confirm'=>false
		];

		$options = array_merge($defaultOptions, $options);

		$this->actions[$title] = $options;

		return $this;
	}

	public function bulkAction($title, $url){
		$this->bulkActions[$title] = [
			'title'=>$title,
			'url'=>$url
		];
		return $this;
	}

	public function processUrlParameters($parameters){		
		$parametersStr = '';
		$parameters['grid'] = $this->id;
		$parametersStr = http_build_query($parameters);

		if(strlen($parametersStr)>0) $parametersStr='?'.$parametersStr;		
		
		return $parametersStr;
	}

	public function checkbox($show, $field){
		$this->checkbox['show'] = $show;
		$this->checkbox['field'] = $field;
		return $this;
	}

	public function getUrl($type = ''){
		$currentUrl = $this->Request->fullUrl();

		if( strpos($currentUrl, '?') !== false)
			$currentUrl = substr($currentUrl, strpos($currentUrl, '?')+1 );
		else
			$currentUrl = '';

		parse_str($currentUrl, $parameters);

		if(isset($parameters['grid']) && $parameters['grid']!=$this->id){
			unset($parameters['page']);
			unset($parameters['search']);
			unset($parameters['order']);
			unset($parameters['direction']);
		}		

		switch ($type) {			
			case 'previous-page':
				if ($this->currentPage>1){
					$parameters['page'] = $this->currentPage-1;					
				}else{					
					$parameters['page'] = 1;
				}		
			break;
			case 'next-page':			
				if($this->currentPage<$this->totalPages){
					$parameters['page'] = $this->currentPage+1;	
				}else{
					$parameters['page'] = $this->currentPage;	
				}	
			break;
			case 'pagination':
				unset($parameters['page']);
			break;	
			case 'advanced-search':
				$parameters['advanced-search'] = 'true';
				unset($parameters['search']);
			break;		
			case 'simple-search':
				unset($parameters['advanced-search']);
				unset($parameters['search']);
			break;
			case 'rows-per-page':
				unset($parameters['rows-per-page']);
			break;
			case 'order':
				unset($parameters['order']);
				unset($parameters['direction']);
			break;
		}

		$url = $this->Request->url().$this->processUrlParameters($parameters);

		return $url;
	}

	public function getFieldsRequest(){
		$currentUrl = $this->getUrl();

		if( strpos($currentUrl, '?') !== false)
			$currentUrl = substr($currentUrl, strpos($currentUrl, '?')+1 );
		else
			$currentUrl = '';

		parse_str($currentUrl, $parameters);		

		return $parameters;

	}

	public function processLine($closure){
		$this->processLineClosure = $closure;
		return $this;
	}

	public function advancedSearch($opts){
		$this->advancedSearch = true;
		$this->advancedSearchFields = $opts;
		foreach($opts as $k=>$v){
			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$k = substr($k, $strrpos+1);
			}			

			if(is_array($v) && isset($v['where']) && $v['where']!==false)
				continue;

			$this->selectFields[$k] = $k;
		}

		foreach ($this->advancedSearchFields as $key => &$field) {
			if(is_string($field)){
				$field = [
					'label'=>ucwords( str_replace('_', ' ', $key) ),
					'type'=>'text'
				];
			}

			if(!isset($field['where']))
				$field['where'] = false;
			else{
				$field['onlySubWhere'] = true;
			}

			if(!isset($field['onlySubWhere']))
				$field['onlySubWhere'] = false;
		}
				
		return $this;
	}

	public function allowExport($bool){
		$this->allowExport = $bool;
		return $this;
	}
	
	public function export($bool){
		$this->export = $bool;
		return $this;
	}

	public function defaultOrder(array $order){		
		$this->defaultOrder[] = [$order[0], (isset($order[1]) ? $order[1] : 'asc')];		
		return $this;
	}

	public function showTrashedLines($bool){
		$this->showTrashedLines = $bool;
		return $this;
	}

	public function make(){		
		$fields = $this->fields;		

		$selectCampos = [];

		
		foreach($fields as $k=>$v){
			if(strpos($v['field'], ' ')!==false){
				$v['field'] = '('.$v['field'].')';
			}
			if($v['field'] <> $v['alias']){
				switch ($this->driverName) {
					case 'odbc':
						$selectCampos[] = $v['field'].' as ['.$v['alias'].']';
					break;
					default:						
						$selectCampos[] = $v['field'].' as '.$v['alias'];
					break;
				}				
			}
			else 
				$selectCampos[] = $v['field'];
		}		

		if(isset($this->actionFields)){
			foreach($this->actionFields as $field){
				$this->extraFields[$field] = $field;
			}
		}					

		foreach($this->advancedSearchFields as $field=>$opts){
			if($opts['where']!==false || $opts['onlySubWhere']===true) continue;
				$this->extraFields[$field] = $field;
		}		

		if(isset($this->extraFields)){
			foreach($this->extraFields as $fieldAdicional){
				$existe = false;
				foreach($this->fields as $field){
					if($field['alias']==$fieldAdicional || $field['field']== $fieldAdicional){
						$existe = true;
						break;
					}
				}
				if(!$existe){
					$selectCampos[] = $fieldAdicional;
				}
			}
		}
				
		for($i=0;$i<count($selectCampos);$i++){
			$selectCampos[$i] = DB::raw($selectCampos[$i]);
		}		
			
		//make a subquery
		$bindings = $this->query->getBindings();
		$subQuery = clone($this->query);
		$subQuery = $subQuery->select($selectCampos);

		$this->query = $this->query->getModel()->newQuery();

		if($this->Request->grid==$this->id){
			//pagination
			$this->currentPage = $this->Request->page ? $this->Request->page : 1;
			
			if(isset($this->Request->search)){
				if(is_string($this->Request->search)){
					//simple search
					$this->searchedValue = htmlentities($this->Request->search);
				
					$whereBusca = '';

					foreach($this->fields as $field=>$label){
						if($strrpos = strrpos($field, '.'))
							$field = substr($field, $strrpos+1);

						switch ($this->driverName) {
							case 'odbc':
								$whereBusca.='+'.$field;
							break;
							case 'sqlsrv':								
								$whereBusca.="+COALESCE(CAST($field AS NVARCHAR(MAX)), '')";
							break;
							default:
								$whereBusca.=",COALESCE($field, '')";
							break;
						}						
					}

					if($whereBusca){
						switch ($this->driverName) {
							case 'odbc':
							case 'sqlsrv':
								//sqlserver < 2012 not have a concat function
								$whereBusca = substr($whereBusca, 1);
							break;
							default:
								$whereBusca = 'CONCAT('.substr($whereBusca, 1).')';
							break;
						}							
						$this->query->where(DB::raw($whereBusca), 'like', '%'.$this->Request->search.'%');

					}
				}else{
					//make where advanced search
					for($i=0;$i<count($this->Request->search);$i++){						

						foreach($this->Request->search[$i] as $field=>$value){							
							if($this->advancedSearchFields[$field]['onlySubWhere']===true)
								$queryBusca =& $subQuery;
							else
								$queryBusca =& $this->query;

							$fieldAux = $field;							

							if(is_string($value)){
								$this->searchedValue[$field] = $value;

								if($value!=='' && $this->advancedSearchFields[$field]['where']===false){	
									if(is_string($this->advancedSearchFields[$field]) || $this->advancedSearchFields[$field]['type']=='text')
										$queryBusca->where($fieldAux, 'like', '%'.$value.'%');
									else									
										$queryBusca->where($fieldAux, $value);
								}
								$valueProcessed = $value;
							}else{
								if(isset($value['from']) && $value['from']!=='')
									$valueAux = $value['from'];
								else
								if(isset($value['to']) && $value['to']!=='')									
									$valueAux = $value['to'];
								else
									$valueAux = '';


								switch ($this->advancedSearchFields[$field]['type']) {
									case 'date':
										//$valueProcessed = Helpers::converteData($valueAux);
									break;
									case 'datetime':
										//$valueProcessed = Helpers::converteDataHora($valueAux);
									break;
									case 'money':
										//$valueProcessed = Helpers::converteMoedaReaisMoney($valueAux);
									break;
									case 'integer':
										$valueProcessed = (int) $valueAux;
									break;
									case 'numeric':
										$valueProcessed = str_replace(',', '.', $valueAux);
									break;
								}

								if(isset($value['from']) && $value['from']!==''){
									$this->searchedValue[$field.'_from'] = $valueAux;
									if($this->advancedSearchFields[$field]['where']===false)
										$queryBusca->where($fieldAux, '>=', $valueProcessed);
								}
								
								if(isset($value['to']) && $value['to']!==''){
									$this->searchedValue[$field.'_to'] = $valueAux;
									if($this->advancedSearchFields[$field]['where']===false)
										$queryBusca->where($fieldAux, '<=', $valueProcessed);
								}
							}

							if($this->advancedSearchFields[$field]['where']){								
								call_user_func($this->advancedSearchFields[$field]['where'], $this, $queryBusca, $valueProcessed, $fieldAux);
							}
						}
					}
				}
			}			

			//advanced search
			if($this->Request['advanced-search']) $this->advancedSearchOpened = true;
		}		

		if(method_exists($subQuery->getModel(), 'getQualifiedDeletedAtColumn')){
			$positionDeletedAt = mb_strpos($subQuery->toSql(), '`'.$subQuery->getModel()->getTable().'`.`deleted_at` ');				
			if($positionDeletedAt!==false && $this->showTrashedLines === false){					
				$deleted_at = mb_substr($subQuery->toSql(), $positionDeletedAt);
				
				if(mb_strpos($deleted_at, ' ')!==false){
					//someone queries have group by
					$deleted_at = mb_substr($deleted_at, 0, mb_strpos($deleted_at, 'null')+4);				
				}				

				$subQuery->whereRaw($deleted_at);									
			}else{
				$subQuery->withTrashed();
			}
			$this->query->withTrashed();
		}

		if($removedScopes = $subQuery->removedScopes()){
			$this->query->withoutGlobalScopes($removedScopes);
		}
		
		$this->query->select('*');		
		
		$bindings2 = $subQuery->getBindings();
		$bindings = $this->query->getBindings();
		$bindings = array_merge($bindings2, $bindings);		

		$this->query->from( DB::raw('('.$subQuery->toSql().') '.$this->query->getModel()->getTable().' ') );

		$this->query->setBindings($bindings);		
		
		//before paginate, count total rows	
		
		$this->totalRows = $this->query->count();
		
		$this->currentRowsPerPage = (int) $this->Request->get('rows-per-page');
		if(!$this->currentRowsPerPage)
			$this->currentRowsPerPage = $this->rowsPerPage[0];

		$this->totalPages = intval(ceil(($this->totalRows/$this->currentRowsPerPage)));

		if($this->currentPage>$this->totalPages)
			$this->currentPage = $this->totalPages;		
		
		//make ordernation		

		if(isset($this->Request->order) && isset($this->Request->direction)){
			if($strrpos = strrpos($this->Request->order, '.'))
				$this->Request->order = substr($this->Request->order, $strrpos+1);

			$this->query->orderBy($this->Request->order, ($this->Request->direction == 'asc' ? 'asc' : 'desc'));
		}else
		if($this->defaultOrder){
			foreach($this->defaultOrder as $order){				
				$this->query->orderBy($order[0], $order[1]);
			}
		}

		if(!$this->export || ($this->export && ($this->Request->get('export')!='xls' && $this->Request->get('export')!='csv')))
			$this->query->skip(($this->currentPage-1)*$this->currentRowsPerPage)->take($this->currentRowsPerPage);		

		//execute builded query
		
		$rows = $this->query->get()->toArray();

		if($this->export && ($this->Request->get('export')=='xls' || $this->Request->get('export')=='csv')){
			array_unshift($rows, $this->fields);
			$excel = \App::make('excel');

			$excel->create($this->id.' - '.Carbon::now(), function($excel) use($rows) {				
			    $excel->sheet('Sheetname', function($sheet) use($rows){
			    	for($i=0; $i<count($rows); $i++){
			    		if($i===0){
			    			$cabecalho = [];
			    			foreach($rows[$i] as $k=>$v){
			    				$cabecalho[] = $v['label'];
			    			}
			    			$sheet->appendRow($cabecalho);
			    		}else{			    			
			    			$sheet->appendRow($rows[$i]);
			    		}
					}			        
			    });

			})->download( $this->Request->get('export') );

		}
	    $nrLines = count($rows);

	    //translate actions
	    if(isset($this->actions)){
	      for($i = 0; $i<$nrLines; $i++){
	        foreach($this->actions as $action){
	          if(strpos($action['url'], '{')!==false){
	            //Have variable to translate
	            foreach($rows[$i] as $field=>$value){   
	              if($field<>'gridActions' && (is_string($value) || is_numeric($value)))
	                $action['url'] = str_replace('{'.$field.'}', $value, $action['url']);
	            }           
	          }
	          $rows[$i]['gridActions'][$action['title']] = $action;
	        }
	      }
	    }

	    if($this->processLineClosure){
	    	for($i = 0; $i<count($rows); $i++){	 
	    		$rows[$i] = call_user_func($this->processLineClosure, $rows[$i]);
	    	}
	    }	    

	    //make
	    
	    $this->view = View::make('Simplegrid::grid', [
	      'rows'=>$rows,
	      'totalRows'=>$this->totalRows,
	      'fields'=>$this->fields,
	      'actions'=>$this->actions,
	      'currentPage'=>$this->currentPage,	      
	      'totalPages'=>$this->totalPages,
	      'id'=>$this->id,	      
	      'searchedValue'=>$this->searchedValue,
	      'fieldsRequest'=>$this->getFieldsRequest(),
	      'urlPagination'=>$this->getUrl('pagination'),
	      'checkbox'=>$this->checkbox,
	      'bulkActions'=>$this->bulkActions,
	      'advancedSearch'=>$this->advancedSearch,
	      'advancedSearchOpened'=>$this->advancedSearchOpened,
	      'advancedSearchFields'=>$this->advancedSearchFields,
	      'currentRowsPerPage'=>$this->currentRowsPerPage,
	      'rowsPerPage'=>$this->rowsPerPage,
	      'export'=>$this->export,
	      'allowExport'=>$this->allowExport,
	      'url'=>$this->getUrl(),
	      'urlOrder'=>$this->getUrl('order'),
	      'urlPreviousPage'=>$this->getUrl('previous-page'),
	      'urlNextPage'=>$this->getUrl('next-page'),	      
	      'urlAdvancedSearch'=>$this->getUrl('advanced-search'),
	      'urlSimpleSearch'=>$this->getUrl('simple-search'),
	      'urlRowsPerPage'=>$this->getUrl('rows-per-page'),
	      'urlExport'=>$this->getUrl('url-export')
	    ]);

	    return $this->view;
	}	

}