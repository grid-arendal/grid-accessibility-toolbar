<?php
error_reporting(E_ALL);set_error_handler(function($l,$m){throw new Exception($m);});define('ABSPATH','/');$options=array();$hooks=array();class GRID_Accessibility_Toolbar{const VERSION='2.0.4';}class GRID_Arendal_Core{const LEGACY=true;}
function add_action($h,$f,$p=10){global$hooks;$hooks[$h][]=$f;}function add_filter(){}function get_option($k,$d=false){global$options;return$options[$k]??$d;}function update_option($k,$v,$a=false){global$options;$options[$k]=$v;}
$root=file_exists(__DIR__.'/../grid-editorial-tools')?__DIR__.'/../grid-editorial-tools':dirname(__DIR__).'/editorial';require $root.'/grid-editorial-tools.php';GRID_Editorial_Tools::boot();
if(!GRID_Arendal_Core::LEGACY||get_option('grid_editorial_origin')!=='legacy'||!isset($hooks['admin_notices']))throw new Exception('Legacy staging must keep existing editor and request widget upgrade');echo "PASS Staged Editorial Tools stands down beside the legacy combined plugin without class collisions\nTOTAL 1\n";
