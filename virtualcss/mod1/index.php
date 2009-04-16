<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Christof Hagedorn <christof.hagedorn@kinea.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once (PATH_t3lib."class.t3lib_tsparser_ext.php");
require_once(PATH_t3lib . 'class.t3lib_page.php');

$LANG->includeLLFile('EXT:virtualcss/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]



/**
 * Module 'Virtual CSS' for the 'virtualcss' extension.
 *
 * @author	Christof Hagedorn <christof.hagedorn@kinea.de>
 * @package	TYPO3
 * @subpackage	tx_virtualcss
 */
class  tx_virtualcss_module1 extends t3lib_SCbase {
				var $pageinfo;

				/**
				 * Initializes the Module
				 * @return	void
				 */
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();
					// $this->id is the value for the active page! (comment by wok)
					$this->id = intval(t3lib_div::_GP("id"));
					$this->perms_clause = $BE_USER->getPagePermsClause(1);
					/*
					if (t3lib_div::_GP('clear_all_cache'))	{
						$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
					}
					*/
				}

function encodePoints($in){
return str_replace(".","__",$in);
}

function decodePoints($in){
return str_replace("__",".",$in);
}


	function templateMenu()	{
		$tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");	// Defined global here!
		$tmpl->tt_track = 0;	// Do not log time-performance information
		$tmpl->init();
		$all = $tmpl->ext_getAllTemplates($this->id,$this->perms_clause);
		$menu='';
		if (count($all)>1)	{
			$this->MOD_MENU['templatesOnPage']=array();
			foreach($all as $d)	{
				$this->MOD_MENU['templatesOnPage'][$d['uid']] = $d['title'];
			}
		}

		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
		$menu = t3lib_BEfunc::getFuncMenu($this->id,'SET[templatesOnPage]',$this->MOD_SETTINGS['templatesOnPage'],$this->MOD_MENU['templatesOnPage']);

		return $menu;
	}

				/**
				 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
				 *
				 * @return	void
				 */
				function menuConfig()	{
					global $LANG;

					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
							'2' => $LANG->getLL('function2'),
							'3' => $LANG->getLL('function3'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
				 */
				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;


					//********************** load conf ***********************
					$template = t3lib_div::makeInstance('t3lib_TStemplate');
					// do not log time-performance information
					$template->tt_track = 0;
					$template->init();
					// Get the root line
					$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
					//$sys_page = t3lib_div::makeInstance('t3lib_page');
					// the selected page in the BE is found
					// exactly as in t3lib_SCbase::init()
					//list($page) = t3lib_BEfunc::getRecordsByField('pages','pid',0);
					//$pageUid = intval($page['uid']);  
					$rootline = $sys_page->getRootLine($this->id);
					// This generates the constants/config + hierarchy info for the template.
					$template->runThroughTemplates($rootline, 0);
					$template->generateConfig();
					$this->conf = $template->setup['plugin.']['virtualcss.']['defaultStyles.'];

					// line added by wok
					$this->useStyleSheet = $template->setup['plugin.']['virtualcss.']['useStyleSheet'];

//					t3lib_div::debug($this->conf);
					//********************** load conf end ***********************
					
					if(!is_array($this->conf) && t3lib_div::_GP('cssubmit') == ''){
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);
						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						//$this->content.= var_export($_REQUEST, true);
						//das hier nat�rlich in die Sprachdatein auslagern.... 
						$this->content.='Please select a page. Also make sure, that there is a typoscript setup with Virtual-CSS support:<br />';
						$this->content.='<strong>In your Root-Template:</strong> you must add the static template: "Virtual Default-Values" in the "Include static (from extensions):" section.';
						$this->content.='<br />To see a running example-config, feel free to insert and modify the following Example-Code in your TypoScript Template Setup:
						';
						
						$this->content.="<pre>
plugin.virtualcss.useStyleSheet=myStyleSheet
plugin.virtualcss{
  defaultStyles{
    myStyleSheet{
      1{
        selector=body
        font-family=Arial
        background-color=#314587
      }
    }
   }
}
						</pre>";

						return false;
						
					}
					foreach ($this->conf as $stylesheet=>$selectors){
//						print("sty:".$stylesheet." ".$selectors."<br/>");
						foreach ($selectors as $selector=>$properties){
//							print("selector:".$selector." ".$properties."<br/>");
							foreach ($properties as $property=>$value){
//								print("property:".$property." ".$value."<br/>");
								if ($property=='selector') $selector=$value;
								if ($property!='selector') $this->style[substr($stylesheet,0,strlen($stylesheet)-1)][$selector][$property]=$value;
							}
						}
					}
					
					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;

					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

							// Draw the header.
						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;


$stylesheet="default";
if (isset($_REQUEST['stylesheet'])) $stylesheet=$_REQUEST['stylesheet'];
// following line added by wok
if (isset($_REQUEST['stylesheettype'])) $stylesheettype=$_REQUEST['stylesheettype'];

// following 6 lines added by wok
if (isset($_REQUEST['delete'])) {
//	print "deleting entries from database ...<br />";
	$query = 'DELETE FROM tx_virtualcss WHERE stylesheet = "'.$stylesheet.'" AND pid="'.$stylesheettype.'"';
//	print $query."<br />";
	$res = mysql(TYPO3_db, $query);
}

if (isset($_REQUEST['cssubmit'])) {
	foreach ($_REQUEST as $key=>$value){
		if (substr($key,0,11)=='virtualcss_'){
//			print "<br/>";
//			print $key."(keyb)<br/>";			
//			print "<br/>";
//			print $key."(keya)<br/>";			
			$key2=substr($key,11,strlen($key));
			$key2=urldecode($this->decodePoints($key2));

			$divider=strpos($key2,'_');
			$sel=substr($key2,0,$divider);
			$prop=substr($key2,$divider+1,strlen($key2)-$divider);
			//			print 'key:'.substr($key,3,strlen($key)).' wert: '.$value;
			//print $sel;
			//$colorpickertypes=array("color","background-color");
			//if (in_array($prop,$colorpickertypes)) $value='#'.$value;


//			print "<br/>";
//			print $key2."(key)<br/>";			
//			print $sel."(sel)<br/>";
//			print $prop."(prop)<br/>";

// query changed by wok
//			$query = 'select count(*) as num from tx_virtualcss WHERE stylesheet = "'.$stylesheet.'" AND selector="'.$sel.'" AND property="'.$prop.'"';
			$query = 'select count(*) as num from tx_virtualcss WHERE stylesheet = "'.$stylesheet.'" AND selector="'.$sel.'" AND property="'.$prop.'" AND pid="'.$stylesheettype.'"';
			$res = mysql(TYPO3_db, $query);
			$line=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
//			print $line['num']."<br>";
//			print $query."<br>";
			if ($line['num']==0){
// following line deactivated by wok and changed line inserted
//				$query = "INSERT INTO `tx_virtualcss` (`uid`, `pid`, `tstamp`, `crdate`, `cruser_id`, `deleted`, `hidden`,`stylesheet`,`selector`,`property`,`value`) VALUES (NULL, '0', '0', '0', '0', '0', '0','".$stylesheet."','".$sel."','".$prop."','".$value."');";
				$query = "INSERT INTO `tx_virtualcss` (`uid`, `pid`, `tstamp`, `crdate`, `cruser_id`, `deleted`, `hidden`,`stylesheet`,`selector`,`property`,`value`) VALUES (NULL, '".$stylesheettype."', '0', '0', '0', '0', '0','".$stylesheet."','".$sel."','".$prop."','".$value."');";
//				print $query."<br>";
				$res = mysql(TYPO3_db, $query);
			} else {
				if ($this->style[$stylesheet][$sel][$prop]!=null){
// following line deactivated by wok and changed line inserted
//					$query = 'UPDATE tx_virtualcss SET value = "'.$value.'" WHERE stylesheet = "'.$stylesheet.'" AND selector="'.$sel.'" AND property="'.$prop.'"';
					$query = 'UPDATE tx_virtualcss SET pid = "'.$stylesheettype.'",value = "'.$value.'" WHERE stylesheet = "'.$stylesheet.'" AND selector="'.$sel.'" AND property="'.$prop.'" AND pid="'.$stylesheettype.'"';
//					print $query."<br>";
				$res = mysql(TYPO3_db, $query);
				}
			}
		}
	}
}


$select_fields='stylesheet,pid,selector,property,value';
$from_table='tx_virtualcss';
$where_clause='pid="0"'; // changed by wok
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
// following line added by wok
								$this->style[$line['stylesheet']]['pid']="0";
// following line deactivated by wok
//								if ($this->style[$stylesheet][$sel][$prop]!=null){
									$this->style[$line['stylesheet']][$line['selector']][$line['property']]=$line['value'];
// following line deactivated by wok
//								}
							}

// Now search the database for entries with the pid = $this->id (=active page) (wok)
$select_fields='stylesheet,pid,selector,property,value';
$from_table='tx_virtualcss';
$where_clause='pid="'.$this->id.'"';
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
// following line added by wok
								$this->style[$line['stylesheet']]['pid']=$line['pid'];
// following line deactivated by wok
//								if ($this->style[$stylesheet][$sel][$prop]!=null){
									$this->style[$line['stylesheet']][$line['selector']][$line['property']]=$line['value'];
// following line deactivated by wok
//								}
							}

						$this->doc->form='<form action="?id='.$this->id.'" method="POST" >';


							// JavaScript
						$this->doc->JScode = '

<link href="colorPicker.css" rel="stylesheet" type="text/css"/>					
<script src="lib/prototype.js" type="text/javascript"></script>
<script src="scriptaculous/scriptaculous.js" type="text/javascript"></script>
<script src="yahoo.color.js" type="text/javascript"></script>
<script src="colorPicker.js" type="text/javascript"></script>

							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
//						$this->content.=$this->doc->spacer(5);
//						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
//$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[stylesheet]',$this->MOD_SETTINGS['stylesheet'],$this->MOD_MENU['stylesheet'])));						
//						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

//						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
//						$this->content.=$this->doc->spacer(5);
//						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				/**
				 * Generates the module content
				 *
				 * @return	void
				 */
				function moduleContent()	{
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
// following 3 lines added from wok
//echo "<pre>";
//print_r ($this->style);
//echo "</pre>";

							$content='</form>
<form name="vcform" action="index.php" method="POST">';
$colorpickertypes=array("color","background-color");

// line changed by wok
//$stylesheet="myStyleSheet";
$stylesheet=$this->useStyleSheet;

if (isset($_REQUEST['stylesheet'])) $stylesheet=$_REQUEST['stylesheet'];

$content.='Choose Stylesheet: <br/><select name="stylesheet" onChange="document.vcform.submit();">';
foreach ($this->style as $key=>$value){
	$content.='<option value="'.$key.'"';
	if ($key==$stylesheet) $content.=' selected="selected" ';
	$content.='>'.$key.'</option>';
}
$content.='</select><br/>';

// following 8 lines added by wok
$content.='<br/>Choose type of stylesheet: <br/><select name="stylesheettype">';
$content.='	<option value="0"';
if (($this->style[$stylesheet]['pid']=="0") OR ($this->style[$stylesheet]['pid']=="")) $content.=' selected="selected"';
$content.='>Template</option>';
$content.='	<option value="'.$this->id.'"';
if (($this->style[$stylesheet]['pid']<>"0") AND ($this->style[$stylesheet]['pid']<>"")) $content.=' selected="selected"';
$content.='>Single page</option>';
$content.='</select><br/><br/>';


$i=0;
foreach ($this->style[$stylesheet] as $selector=>$val){
// condition added by wok: suppress "pid"
if($selector <> "pid") {
	$content.= "<br/>".$selector."<br/>";	

	foreach ($val as $property=>$value){
//		if ($this->style[substr($stylesheet,0,strlen($stylesheet)-1)][substr($selector,0,strlen($selector)-1)][$property]!=null){
//print			$selector."<br/>";
			$content.='<input id="virtualcss_'.urlencode($this->encodePoints($selector)).'_'.$property.'" name="virtualcss_'.urlencode($this->encodePoints($selector)).'_'.$property.'" class="textfield" type="text" style="width:100px" value="'.$value.'"/>';		
			if (in_array($property,$colorpickertypes)){

				$content.='<button style="margin-left:0px;width: 18px; height: 18px; border: 1px outset #666;" id="'.$i.$property.'_box" class="colorbox"></button> 
			<script type="text/javascript">
				new Control.ColorPicker("virtualcss_'.urlencode($this->encodePoints($selector)).'_'.$property.'", { "swatch" : "'.$i.$property.'_box" });
			</script>';
			}
			$i++;
			$content.='&nbsp;'.$property.'<br/>';
//		}
	}

} // end of condition (suppress "pid") added by wok
}


$content.='
<br/><input type="hidden" name="id" value="'.$this->id.'">';
// Delete button added by wok
$content.='
<br/><input type="submit" name="cssubmit" value=" Save ">&nbsp;<input type="submit" name="delete" value=" Delete ">
';
							$this->content.=$this->doc->section('',$content,0,1);
						break;
						case 2:

							$content='<div align=center><strong>Menu item #2...</strong></div>';
							$this->content.=$this->doc->section('Message #2:',$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content.=$this->doc->section('Message #3:',$content,0,1);
						break;
					}
				}
			}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/virtualcss/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/virtualcss/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_virtualcss_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>