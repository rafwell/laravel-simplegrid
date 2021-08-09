<?php

namespace Rafwell\Simplegrid\Helpers;

class StringUtil
{

	public static function brazilianNumberToFloat($numero)
	{
		$retorno = preg_replace('/[^0-9,\.-]/', '', $numero);
		if ($retorno === '') return '';
		$retorno = str_replace('.', '', $retorno);
		$retorno = str_replace(',', '.', $retorno);
		return (float) $retorno;
	}
}
