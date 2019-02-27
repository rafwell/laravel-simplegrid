<?php
namespace Rafwell\Simplegrid\Export;

use Illuminate\View\View;
use Exception;
use Rafwell\Simplegrid\Grid;

class Pdf{
    protected $view;
    protected $pdf;
    protected $config;
    protected $fileName;
    protected $name;
    protected $filePath;
    protected $rows;
    protected $headers;

    public function __construct(Grid $grid){
        $this->config = $grid->simpleGridConfig['export']['pdf']['snappy'];
        
        $rowsPerPageExport = 1000000;
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
            }

            $this->headers = $header;

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
                $row = array_map('trim', $row);			
                
                $this->rows[] = $row;				
            }
        }

        //TODO
        //$pdfHeader = view()->make('Simplegrid::export.pdf.header',[])->render();
        $pdfFooter = view()->make('Simplegrid::export.pdf.footer')->render();

        $pdfBody = view()->make('Simplegrid::export.pdf.body', [
            'rows'=>$this->rows,
            'headers'=>$this->headers,
            'bootstrapCss'=>$grid->simpleGridConfig['export']['pdf']['bootstrapCss']
        ])->render();
        
        $this->pdf = (app('snappy.pdf.wrapper'))->loadHTML( $pdfBody )
            ->setOption('footer-html', $pdfFooter)
            ->setOptions( $this->config );

        $this->filePath = tempnam( sys_get_temp_dir(), 'laravel-simplegrid-export-pdf' );
        
        unlink($this->filePath);

        $this->pdf->save($this->filePath);
    }

    public function getFilePath(){
        return $this->filePath;
    }

    public function getFileContent(){
        return file_get_contents( $this->getFilePath() );
    }

    public function getFileName(){
        return $this->filePath;
    }
}