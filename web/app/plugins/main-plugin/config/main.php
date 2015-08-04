<?php

return [
	'bootstrap' => [
		'social', 'debug'
	],
	'modules' => [
		'social' => [
			'class' => 'LM\Modules\Social\Social_Links',
			'social_networks' => [ 'facebook', 'google_plus', 'vk' ]
		],
		'debug' =>[
			'class' => 'LM\Modules\Debug\Debug'
		]
	],
];