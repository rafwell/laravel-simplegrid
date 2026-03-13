<?php

namespace Rafwell\Simplegrid\Exceptions;

use Exception;

class GridExportNotAllowedException extends Exception
{
	public function __construct(string $message = 'Exportação não permitida.', int $code = 403, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
