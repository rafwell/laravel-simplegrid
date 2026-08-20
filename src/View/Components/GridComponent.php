<?php

namespace Rafwell\Simplegrid\View\Components;

use Illuminate\View\Component;
use Rafwell\Simplegrid\Grid;

class GridComponent extends Component
{
	public function __construct(public Grid $grid)
	{
	}

	public function render()
	{
		return $this->grid->make();
	}
}
