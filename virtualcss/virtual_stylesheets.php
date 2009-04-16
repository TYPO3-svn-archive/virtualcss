<?php

header('Content-type: text/css');

					//********************** load conf ***********************
					$template=$GLOBALS["TSFE"]->tmpl;
					$this->conf = $template->setup['plugin.']['virtualcss.'];
//					t3lib_div::debug($this->conf);
					//********************** load conf end ***********************
					
					$stylesheetsel = $template->setup['plugin.']['virtualcss.']['useStyleSheet'];
// following line changed and activated by wok
					print "/*".$stylesheetsel."*/\n\n";

					// get the actual pid (added by wok)
					$this->id = intval(t3lib_div::_GP("id"));
					
					$this->confStyle = $template->setup['plugin.']['virtualcss.']['defaultStyles.'][$stylesheetsel."."];
//					t3lib_div::debug($this->confStyle);
						
							foreach ($this->confStyle as $selector=>$properties){
//							print("selector:".$selector." ".$properties."<br/>");
							foreach ($properties as $property=>$value){
//								print("property:".$property." ".$value."<br/>");
								if ($property=='selector') $selector=$value;
								if ($property!='selector') $this->style[$stylesheetsel][$selector][$property]=$value;
							}
						}


// FIRST READ DATABASE VALUES FOR TEMPLATE (added by wok)
// pid added in following line by wok
$select_fields='pid,stylesheet,selector,property,value';
$from_table='tx_virtualcss';
// following line uncommented and added new whereclause by wok
//$where_clause='stylesheet="'.$stylesheetsel.'"';
$where_clause='stylesheet="'.$stylesheetsel.'" AND pid="0"';
$groupBy='';
$orderBy='';
$limit='';

$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery( $select_fields
                                                , $from_table
                                                , $where_clause
                                                , $groupBy
                                                , $orderBy
                                                , $limit
                                                );

while ($line=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	$this->style[$line['stylesheet']][$line['selector']][$line['property']]=$line['value'];
}


// NOW READ DATABASE VALUES FOR ACTUAL PAGE (added by wok)
// pid added in following line by wok
$select_fields='pid,stylesheet,selector,property,value';
$from_table='tx_virtualcss';
// following line uncommented and added new whereclause by wok
//$where_clause='stylesheet="'.$stylesheetsel.'"';
$where_clause='stylesheet="'.$stylesheetsel.'" AND pid="'.$this->id.'"';
$groupBy='';
$orderBy='';
$limit='';

$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery( $select_fields
                                                , $from_table
                                                , $where_clause
                                                , $groupBy
                                                , $orderBy
                                                , $limit
                                                );

while ($line=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
	$this->style[$line['stylesheet']][$line['selector']][$line['property']]=$line['value'];
}



foreach ($this->style as $stylesheet=>$selectors){
	foreach ($selectors as $selector=>$properties){
		print $selector." {\n";
		foreach ($properties as $property=>$value){
			print $property.":".$value.";\n";
		}
		print "}\n\n";
	}
}
?>