<?php
// Credential-free update policy tests, without network access.
error_reporting(E_ALL);set_error_handler(function($level,$message){throw new Exception($message);});
define('ABSPATH',__DIR__.'/');function add_action(){}function add_filter(){} function is_wp_error($value){return $value instanceof Exception;}
require dirname(__DIR__,2).'/includes/class-grid-plugin-updates.php';
require dirname(__DIR__,2).'/vendor/plugin-update-checker/plugin-update-checker.php';
$count=0;function ok($label,$value){global $count;if(!$value)throw new Exception('FAIL '.$label);$count++;echo 'PASS '.$label."\n";}
ok('Enabled without any credential',GRID_A11Y_Plugin_Updates::enabled());
ok('Metadata uses public CDN rather than API',GRID_A11Y_Plugin_Updates::METADATA_URL==='https://raw.githubusercontent.com/grid-arendal/grid-accessibility-toolbar/main/updates.json');
$version='1.11.0';$url='https://github.com/grid-arendal/grid-accessibility-toolbar/releases/download/v1.11.0/grid-accessibility-toolbar-1.11.0.zip';
$info=\YahnisElsts\PluginUpdateChecker\v6p0\Plugin\PluginInfo::fromJson(json_encode(['name'=>'GRID Accessibility Toolbar','slug'=>'grid-accessibility-toolbar','version'=>$version,'download_url'=>$url,'sections'=>['description'=>'Reading preferences']]));
ok('Actual library metadata parser is compatible',is_object($info));ok('Matching published package accepted',GRID_A11Y_Plugin_Updates::valid_package($info)===$info);
foreach(['1.11.0-beta','main','v1.11.0','1.11',null,[],1.11] as $bad)ok('Malformed or development version rejected',GRID_A11Y_Plugin_Updates::valid_package((object)['version'=>$bad,'download_url'=>$url])===null);
foreach([$url.'?token=secret',str_replace('https:','http:',$url),str_replace('github.com','evil.example',$url),str_replace('/grid-arendal/','/other/',$url),str_replace('/v1.11.0/','/v1.10.0/',$url),str_replace('-1.11.0.zip','-1.10.0.zip',$url),'https://github.com/grid-arendal/grid-accessibility-toolbar/archive/refs/heads/main.zip',[],null] as $bad)ok('Unsafe, mismatched or source package rejected',GRID_A11Y_Plugin_Updates::valid_package((object)['version'=>$version,'download_url'=>$bad])===null);
ok('Unrelated plugin slug rejected',GRID_A11Y_Plugin_Updates::valid_package((object)['slug'=>'other-plugin','version'=>$version,'download_url'=>$url])===null);
ok('Missing metadata rejected',GRID_A11Y_Plugin_Updates::valid_package(null)===null);
define('GRID_A11Y_ENABLE_UPDATES',false);ok('Server opt-out respected',!GRID_A11Y_Plugin_Updates::enabled());
echo 'TOTAL '.$count."\n";
