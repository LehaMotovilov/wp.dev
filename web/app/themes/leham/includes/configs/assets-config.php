<?php

	/**
	 * Contains all assets files.
	 */
	return [
		'css' => [
			'main' => [
				'type' => 'file',
				'condition' => 'all',
				'deps' => []
			],
			'font' => [
				'type' => 'url',
				'condition' => 'all',
				'deps' => [],
				'file' => '//fonts.googleapis.com/css?family=Roboto:400,700&amp;subset=latin,cyrillic'
			]
		],
		'js' => [
			'main' => [
				'type' => 'file',
				'condition' => 'all',
				'deps' => [ 'jquery' ]
			]
		]
	];