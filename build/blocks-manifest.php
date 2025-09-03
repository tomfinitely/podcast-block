<?php
// This file is generated. Do not modify it manually.
return array(
	'podcast-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'podcast-block/podcast-block',
		'version' => '0.1.0',
		'title' => 'Podcast Block',
		'category' => 'widgets',
		'icon' => 'microphone',
		'description' => 'A block that fetches and displays podcasts from Spotify, Overcast, and other podcast platforms.',
		'example' => array(
			
		),
		'attributes' => array(
			'profileUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'platform' => array(
				'type' => 'string',
				'default' => 'spotify'
			),
			'quantity' => array(
				'type' => 'number',
				'default' => 5
			),
			'platformLinks' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'isLoading' => array(
				'type' => 'boolean',
				'default' => false
			),
			'error' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'innerBlocks' => true
		),
		'textdomain' => 'podcast-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);
