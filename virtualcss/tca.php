<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_virtualcss"] = array (
	"ctrl" => $TCA["tx_virtualcss"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,stylesheet,selector,property,value"
	),
	"feInterface" => $TCA["tx_virtualcss"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"stylesheet" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:virtualcss/locallang_db.xml:tx_virtualcss.stylesheet",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"selector" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:virtualcss/locallang_db.xml:tx_virtualcss.selector",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"property" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:virtualcss/locallang_db.xml:tx_virtualcss.property",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
		"value" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:virtualcss/locallang_db.xml:tx_virtualcss.value",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "trim",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, stylesheet, selector, property, value")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>