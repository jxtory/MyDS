<?php
	return [
		//mode = regex || DOM
		'mode' => '',
		
		'regex' => [
			'link' => '/<a[^>]+?href=["\']?([^"\']+)[\"\']?[^>]*>([^<]*十九大[^<]*)<\/a>/i',
		],

		'rxopt' => [
			0
		],

		'DOM' => [
		],
		'Domopt' => [],

	];
?>