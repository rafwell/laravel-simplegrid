<?php
namespace Rafwell\Simplegrid;
use Rafwell\Simplegrid\Query\QueryBuilder;
use Illuminate\Http\Request;
use DB;
use Rafwell\Grid\Helpers;
use Carbon\Carbon;
use View;
use Exception;

class Grid{
	private $view;	
	public $id;
	public $fields;
	public $actionFields = [];
	public $extraFields = [];
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
	public $rowsPerPage = [];
	public $currentRowsPerPage = 10;
	public $processLineClosure;
	public $export = true;
	public $showTrashedLines = false;
	public $defaultOrder = []; //['field', 'direction']	
	private $allowExport = true;
	private $simpleGridConfig;
	private $queryBuilder;

	function __construct($query, $id, $config = []){		
		//merge the configurations
		$this->simpleGridConfig = include __DIR__.'/../config/rafwell-simplegrid.php';		
		$this->simpleGridConfig = array_merge($this->simpleGridConfig, config('rafwell-simplegrid'));				
		$this->simpleGridConfig = array_merge($this->simpleGridConfig, $config);

		$this->rowsPerPage = $this->simpleGridConfig['rowsPerPage'];
		$this->currentRowsPerPage = $this->simpleGridConfig['currentRowsPerPage'];
		$this->allowExport = $this->simpleGridConfig['allowExport'];		

		$this->queryBuilder = (new QueryBuilder($query))->getBuilder();
		$this->id = $id;				
		$this->Request = Request::capture();		
		return $this;
	}

	public function fields($fields){
		

		foreach($fields as $k=>&$v){
			if(is_string($v)){
				$v = [
					'label'=>$v,
					'field'=>$k,
					'alias'=>$k					
				];
			}else{
				if(!isset($v['label']))
					$v['label'] = ucwords( str_replace('_', ' ', $k) );

				$v['alias'] = $k;
			}


			$fields[$k] = $v;

			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$v['alias_after_query_executed'] = substr($k, $strrpos+1);				
			}else{
				$v['alias_after_query_executed'] = $k;
			}

			$v['show'] = true;			

			$this->selectFields[$k] = $k;
		}				

		foreach($fields as $k=>&$v){
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
		$actionFields = $fields;

		foreach($fields as $k){
			$this->actionFields[$k] = [
				'label'=>$k,
				'field'=>$k,
				'alias'=>$k,
				'show'=>false			
			];

			$strrpos = strrpos($k, '.');
			if($strrpos!==false){
				$this->actionFields[$k] ['alias_after_query_executed'] = substr($k, $strrpos+1);				
			}else{
				$this->actionFields[$k] ['alias_after_query_executed'] = $k;
			}

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

			if(!isset($field['options']))
				$field['options'] = [];	
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

	public function validateFields(){
		foreach($this->advancedSearchFields as $field=>$opts){
			if(!isset( $this->fields[$field] ) && !isset($this->actionFields[$field]))
				throw new Exception('The field "'.$field.'" in advancedSearch must exists in fields or actionFields array');
				
		}
	}

	public function make(){
		$this->validateFields();

		//process all fields needed to run this grid
		$this->queryBuilder->processUsedFields($this->fields, $this->actionFields, $this->advancedSearchFields);		
		
		//if have 2 grids in same page, the search, ordenation, etc, will work only for the last action

		if($this->Request->grid==$this->id){
			if(isset($this->Request->search)){
				if(is_string($this->Request->search)){					
					//make where simple search
					$this->queryBuilder->performSimpleSearch( $this->Request->search );
				}else{
					//make where advanced search
					$this->queryBuilder->performAdvancedSearch($this->Request->search, $this->advancedSearchFields, $this->simpleGridConfig['advancedSearch']);
				}
			}			

			//advanced search
			if($this->Request['advanced-search']) $this->advancedSearchOpened = true;

			//sort
			if(isset($this->Request->order) && isset($this->Request->direction)){				
				$this->queryBuilder->sort($this->Request->order, ($this->Request->direction == 'asc' ? 'asc' : 'desc'));
			}else{
				if($this->defaultOrder)
					$this->queryBuilder->sort($this->defaultOrder[0][0], ($this->defaultOrder[0][1] == 'asc' ? 'asc' : 'desc'));				
			}
		}else{
			if($this->defaultOrder)
				$this->queryBuilder->sort($this->defaultOrder[0][0], ($this->defaultOrder[0][1] == 'asc' ? 'asc' : 'desc'));				
		}
		
		
		//before paginate, count total rows	
		$this->totalRows = $this->queryBuilder->getTotalRows();
		
		//paginate
		if($this->Request->get('rows-per-page')){
			$getRowsperPage = (int) $this->Request->get('rows-per-page');
			if(array_search($getRowsperPage, $this->rowsPerPage)!==false)
				$this->currentRowsPerPage = $getRowsperPage;
		}

		$this->currentPage = $this->Request->page ? $this->Request->page : 1;

		$this->totalPages = intval(ceil(($this->totalRows/$this->currentRowsPerPage)));

		if($this->currentPage>$this->totalPages)
			$this->currentPage = $this->totalPages;		

		if(!$this->export || ($this->export && ($this->Request->get('export')!='xls' && $this->Request->get('export')!='csv'))){
			$this->queryBuilder->paginate($this->currentRowsPerPage, $this->currentPage);
		}

		//execute builded query
		
		$rows = $this->queryBuilder->performQueryAndGetRows();		

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
	      'searchedValue'=>$this->queryBuilder->getSearchedValue(),
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
	      'urlExport'=>$this->getUrl('url-export'),
	      'simpleGridConfig'=>$this->simpleGridConfig
	    ]);

	    return $this->view;
	}	

}