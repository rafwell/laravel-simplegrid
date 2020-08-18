<?php
namespace Rafwell\Simplegrid\Export;

use Rafwell\Simplegrid\Grid;
use Illuminate\View\View;
use Exception;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Rafwell\Simplegrid\Helpers\StringUtil;

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

        $style = (new StyleBuilder())
           ->setShouldWrapText(false)
           ->build();

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
                                    
                $this->writer->addRowWithStyle($header, $style);	   
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
                    $column = str_replace('R$ ', 'R$', $column);		
                    $column = html_entity_decode($column, null, 'UTF-8');	

                    if($this->isBrazilianMoneyFormat($column)){
                        $column = $this->normalizeBrazilianMoney($column);
                    }			
                    
                    if(is_string($column)){
                        $column = trim($column);
                        $column = strip_tags($column);
                    }
                }
                
                $this->writer->addRowWithStyle( $row, $style );				
            }
        }
       
        $this->writer->close();
    }

    public function getContentType(){
        switch ($this->fileExt) {
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';	
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

    protected function isBrazilianMoneyFormat($string){
        $res = preg_match('/R\$[0-9\.]*\,[0-9$]*/', $string);
        return $res > 0;
    }

    protected function normalizeBrazilianMoney($string){
        return StringUtil::brazilianNumberToFloat($string);
    }
}