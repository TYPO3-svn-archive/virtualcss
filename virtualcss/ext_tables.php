<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_virtualcss"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:virtualcss/locallang_db.xml:tx_virtualcss',		
		'label'     => 'uid',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_virtualcss.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, stylesheet, selector, property, value",
	)
);


if (TYPO3_MODE == 'BE')	{
		
	t3lib_extMgm::addModule('web','txvirtualcssM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/Virtual_CSS_Default_Values/', 'Virtual CSS Default Values');
?>