<?php
return [
	'allowExport'=>true, //if true, show the export option, maybe not a good idea for big grids or low server
	'export'=>[
		'pdf'=>[
			'enabled'=>false,
			'bootstrapCss'=>'',
			'snappy'=>[
				'orientation'=>'landscape'
			]
		]
	],
	'rowsPerPage'=>[10,20,30,50,100,200], //the options to select
	'currentRowsPerPage'=>10, //the initial value by default - must exists in rowsPerPage
	'advancedSearch'=>[
		'formats'=>[
			'date'=>[
				//allow translate the date visual format to backend format. the index 0 is the js format (moment) and the index 1 is the php format (carbon)
				/*
					Example for Brazil standard
					'input'=>['DD/MM/YYYY', 'd/m/Y'], 
					'processTo'=>['YYYY-MM-DD', 'Y-m-d']
				*/
				'input'=>['YYYY-MM-DD', 'Y-m-d'], 
				'processTo'=>['YYYY-MM-DD', 'Y-m-d']
			],
			'datetime'=>[
				//allow translate the date visual format to backend format. the index 0 is the js format (moment) and the index 1 is the php format (carbon)
				/*
					Example for Brazil standard					
					'input'=>['DD/MM/YYYY HH:mm:ss', 'd/m/Y H:i:s'], 
					'processTo'=>['YYYY-MM-DD HH:mm:ss', 'Y-m-d H:i:s']
				*/
				'input'=>['YYYY-MM-DD HH:mm:ss', 'd/m/Y H:i:s'], 
				'processTo'=>['YYYY-MM-DD HH:mm:ss', 'Y-m-d H:i:s']
			]
		]
	],
	'paginationType'=>'pills', //select | pills
];