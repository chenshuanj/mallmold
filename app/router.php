<?php
return array (
	'type' => 1,
	'*/*' => array (
		'scheme' => 'http',
		'host' => '',
		'query' => '',
		'rewrite' => '',
	),
	'goods/*' => array (
		'rewrite' => 'goods/index',
	),
	'goods/index' => array (
		'rewrite' => '',
	),
	'goods/comment' => array (
		'rewrite' => '',
	),
	'catalog/*' => array (
		'rewrite' => 'catalog/index',
	),
	'catalog/index' => array (
		'rewrite' => '',
	),
	'catalog/search' => array (
		'rewrite' => '',
	),
	'article/*' => array (
		'rewrite' => 'article/index',
	),
	'list/*' => array (
		'rewrite' => 'article/cate',
	),
	'page/*' => array (
		'rewrite' => 'page/index',
	),
)
?>