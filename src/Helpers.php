<?php
namespace Rafwell\Easygrid;

class Helpers{
	/**
	 * Converte uma string em format de número brasileiro (15,30) para o formato number (15.30)
	 * @return string
	 */
	public static function converteNumeroNumber($numero){
		$retorno = preg_replace('/[^0-9,\.-]/', '', $numero);
		if($retorno==='') return '';
		$retorno = str_replace('.', '', $retorno);
		$retorno = str_replace(',', '.', $retorno);
		return $retorno;
	}

	/**
	 * Converte uma string em formato number(15.30) para o formato de número brasileiro (15,30)
	 * @return string
	 */
	public static function converteNumberNumero($number){
		if($number === '') return '';
		return number_format($number, 2, ',', '.');
	}

	/**
	 * Converte uma string em format de moeda brasileira (R$ 15,30) para o formato money (15.30)
	 * @return string
	 */
	public static function converteMoedaReaisMoney($moedaBR){
		return self::converteNumeroNumber($moedaBR);
	}

	/**
	 * Converte uma string em formato money(15.30) para o formato de moeda brasileira (R$ 15,30)
	 * @return string
	 */
	public static function converteMoedaMoneyReais($moeda){
		return 'R$ '.self::converteNumberNumero($moeda);
	}

	/**
	 * Converte uma string data no formato brasileiro para o formato americano e vice versa
	 * @return string
	 */
	public static function converteData($string){
		if(!$string || !is_string($string))
			return $string;

		if(preg_match('/([0-9][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9])/', $string)){
			$retorno = implode('-',array_reverse(explode('/', $string)));
		}else
		if(preg_match('/([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9])/', $string)){
			$retorno = implode('/',array_reverse(explode('-', $string)));	
		}else{			
			throw new \Exception("String inválida para conversão para data!");
		}		

		return $retorno;
	}

	/**
	 * Converte uma string data/hora no formato brasileiro para o formato americano e vice versa
	 * @return string
	 */
	public static function converteDataHora($string, $segundos = false){
		if(!$string || !is_string($string))
			return $string;				
		
		if(preg_match('/([0-9][0-9]\/[0-9][0-9]\/[0-9][0-9][0-9][0-9]\ [0-9][0-9]:[0-9][0-9](\:[0-9][0-9])?)/', $string)){
			$retorno = explode(' ', $string);
			$retorno = implode('-',array_reverse(explode('/', $retorno[0]))).' '.$retorno[1];
		}else
		if(preg_match('/([0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]\ [0-9][0-9]:[0-9][0-9](\:[0-9][0-9])?)/', $string)){
			$retorno = explode(' ', $string);
			$retorno = implode('/',array_reverse(explode('-', $retorno[0]))).' '.$retorno[1];	
		}else{			
			throw new \Exception("String inválida para conversão para data/hora!");
		}
		if($segundos === false)
			$retorno = substr($retorno, 0, 16);
		return $retorno;
	}
}