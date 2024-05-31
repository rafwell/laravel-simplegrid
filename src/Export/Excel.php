<?php

namespace Rafwell\Simplegrid\Export;

use Rafwell\Simplegrid\Grid;
use Illuminate\View\View;
use Exception;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Rafwell\Simplegrid\Helpers\StringUtil;
use Log;

class Excel
{
    protected $writer;
    protected $fileName;
    protected $fileExt;
    protected $filePath;

    public function __construct($ext, Grid $grid)
    {
        if ($ext == 'xls') {
            $this->writer = WriterEntityFactory::createWriter(Type::XLSX);
            $this->fileExt = 'xlsx';
        } else
        if ($ext == 'csv') {
            $this->writer = WriterEntityFactory::createWriter(Type::CSV);
            $this->fileExt = 'csv';
        }

        $style = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->filePath = tempnam(sys_get_temp_dir(), 'simplegrid-export');
        $this->fileName = $grid->id . '-export-' . date('Y-m-d-H:i:s') . '.' . $this->fileExt;
        $this->writer->openToFile($this->filePath);

        $rowsPerPageExport = $grid->simpleGridConfig['export']['excel']['rowsPerPageExport'];

        $totalPagesExport = ceil($grid->totalRows / $rowsPerPageExport); //itens per query

        $fieldsNamesAfterQuery = array_flip(collect($grid->fields)->pluck('alias_after_query_executed')->toArray());


        for ($i = 1; $i <= $totalPagesExport; $i++) {

            $cloneBuilder = clone $grid->queryBuilder;

            $cloneBuilder->paginate($rowsPerPageExport, $i);

            $rows = $cloneBuilder->performQueryAndGetRows();
            $rows = $grid->translateActions($rows);

            unset($cloneBuilder);

            if ($i === 1) {
                $header = [];
                $rowsHeader = [$rows[0]];
                array_unshift($rowsHeader, $grid->fields);
                foreach ($rowsHeader[0] as $field => $value) {
                    $header[] = $grid->fields[$field]['label'];
                }

                $exportRow = WriterEntityFactory::createRowFromArray($header, $style);
                $this->writer->addRow($exportRow);
            }


            if ($grid->processLineClosure) {
                for ($j = 0; $j < count($rows); $j++) {
                    $rows[$j] = call_user_func($grid->processLineClosure, $rows[$j]);
                }
            }

            foreach ($rows as $k => $row) {
                $row = array_intersect_key($row, $fieldsNamesAfterQuery);
                //Clear html before export

                foreach ($row as &$column) {
                    $column = str_replace('&nbsp;', ' ', $column);
                    $column = str_replace(['<br>', '<br/>'], "\r", $column);
                    $column = str_replace('R$ ', 'R$', $column);
                    $column = html_entity_decode($column, null, 'UTF-8');

                    if ($this->isBrazilianMoneyFormat($column)) {
                        $column = $this->normalizeBrazilianMoney($column);
                    }

                    if (is_string($column)) {
                        $column = trim($column);
                        $column = strip_tags($column);
                    }
                }

                $exportRow = WriterEntityFactory::createRowFromArray($row, $style);
                $this->writer->addRow($exportRow);
                
            }
        }

        $this->writer->close();
    }

    public function getContentType()
    {
        switch ($this->fileExt) {
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                break;
            case 'csv':
                return 'text/csv';
                break;
        }
    }

    public function getFilePath()
    {
        return $this->filePath;
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function getFileContent()
    {
        return file_get_contents($this->getFilePath());
    }

    protected function isBrazilianMoneyFormat($string)
    {
        // Check if the string matches the Brazilian money format for positive and negative values
        $matchesMoneyFormat = preg_match('/^R\$[ ]?-?[0-9]{1,3}(?:\.[0-9]{3})*(?:,[0-9]{2})?$/', $string);

        return $matchesMoneyFormat;
    }

    protected function normalizeBrazilianMoney($string)
    {
        return StringUtil::brazilianNumberToFloat($string);
    }
}
