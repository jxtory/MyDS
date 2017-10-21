<?php
	return [
		//mode = regex || DOM
		'mode' => '',
		
		'regex' => [
			'link' => '/<a[^>]+?href=["\']?([^"\']+)[\"\']?[^>]*>([^<]*十九大[^<]*)<\/a>/i',
			'p' => '/<p>([^<]+)<\/p>/i',
		],

		'rxopt' => [
			0, 1
		],

		'DOM' => [
		],
		'Domopt' => [],

	];
?>