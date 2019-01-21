<?php
namespace Rafwell\Simplegrid\Export;

use Rafwell\Simplegrid\Grid;
use Illuminate\View\View;
use Exception;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

class Excel{
    protected $writer;
    protected $fileName;
    protected $fileExt;
    protected $filePath;

    public function __construct($ext, Grid $grid){
        if($ext == 'xls'){
            $this->writer = WriterFactory::create(Type::XLSX);
            $this->fileExt = 'xlsx'; 
        }else
        if($ext == 'csv'){
            $this->writer = WriterFactory::create(Type::CSV);
            $this->fileExt = 'csv';
        }

        $this->filePath = tempnam(sys_get_temp_dir(), 'simplegrid-export');
        $this->fileName = $grid->id.'-export-'.date('Y-m-d-H:i:s').'.'.$this->fileExt;
        $this->writer->openToFile($this->filePath);

        $rowsPerPageExport = 10000;
        $totalPagesExport = ceil($grid->totalRows/$rowsPerPageExport); //itens per query
        
        $fieldsNamesAfterQuery = array_flip(collect($grid->fields)->pluck('alias_after_query_executed')->toArray());
				
        for($i = 1; $i<=$totalPagesExport; $i++){				
            $grid->queryBuilder->paginate($rowsPerPageExport, $i);
            $rows = $grid->queryBuilder->performQueryAndGetRows();		

            if($i===1){
                $header = [];
                $rowsHeader = [$rows[0]];
                array_unshift($rowsHeader, $grid->fields);
                foreach($rowsHeader[0] as $field=>$value){
                    $header[] = $grid->fields[$field]['label'];
                }
                                    
                $this->writer->addRow($header);	    			
            }

            if($grid->processLineClosure){
                for($i = 0; $i<count($rows); $i++){	 
                    $rows[$i] = call_user_func($grid->processLineClosure, $rows[$i]);
                }
            }

            foreach($rows as $k=>$row){						
                $row = array_intersect_key($row, $fieldsNamesAfterQuery);
                //Clear html before export
                
                foreach($row as &$column){
                    $column = str_replace("\xA0", ' ', $column);	
                    $column = str_replace('&nbsp;', ' ', $column);							
                    $column = str_replace(['<br>','<br/>'], "\r", $column);		
                    $column = html_entity_decode($column, null, 'UTF-8');					
                }
                                    
                $row = array_map('strip_tags', $row);					
                
                $this->writer->addRow( $row );				
            }
        }
       
        $this->writer->close();
    }

    public function getContentType(){
        switch ($this->fileExt) {
            case 'xlsx':
                return 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';	
            break;
            case 'csv':
                return 'text/csv';
            break;
        }
    }

    public function getFilePath(){
        return $this->filePath;
    }

    public function getFileName(){
        return $this->fileName;
    }

    public function getFileContent(){
        return file_get_contents( $this->getFilePath() );
    }
}