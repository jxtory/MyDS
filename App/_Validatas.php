<?php
	return [
		//mode = regex || DOM
		'mode' => '',
		
		'regex' => [
			'link' => '/<a[^>]+?href=["\']?([^"\']+)[\"\']?[^>]*>([^<]+)<\/a>/i',
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