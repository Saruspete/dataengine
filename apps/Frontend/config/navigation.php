<?php

$aMenu = array(
	'navbar-left' => array(
		'Admin' => array(
			'position'	=> 99,
			'routePath'	=> '/',
			'children'	=> array(

				'Users' => array(
				),
				'Groups' => array(
				)
			)
		),
	),
	'navbar-right' => array(
		'Session' => array(
			'position'	=> 999
		),
		'Search' => array(
			'caption'	=> '<input type="text" name="search" style="border-top-width: 0px; border-bottom-width: 0px;" />',
		),
	),
);

return $aMenu;