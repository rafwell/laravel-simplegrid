<?php

namespace Rafwell\Simplegrid\Contracts;

use Rafwell\Simplegrid\Grid;

interface GridExportGateInterface
{
    /**
     * Define se o usuário pode exportar o grid.
     *
     * @param Grid $grid Instância do grid (contém $id, $Request, etc)
     * @return bool
     */
    public function authorize(Grid $grid): bool;
}
