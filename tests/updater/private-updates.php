<?php
// No real credentials or network calls. Exercises the actual pinned GitHub API class.
error_reporting(E_ALL);
set_error_handler(function($level,$message){throw new Exception($message);});
define('ABSPATH',__DIR__.'/');
function add_action(){} function add_filter(){} function do_action(){}
function wp_parse_url($url,$component=-1){return parse_url($url,$component);}
function is_wp_error($value){return $value instanceof Exception;}
require dirname(__DIR__,2).'/includes/class-grid-private-updates.php';
require dirname(__DIR__,2).'/vendor/plugin-update-checker/plugin-update-checker.php';
$count=0;function ok($label,$value){global $count;if(!$value)throw new Exception('FAIL '.$label);$count++;echo 'PASS '.$label."\n";}
putenv('GRID_A11Y_GITHUB_TOKEN');ok('No credential leaves updater disabled',!GRID_A11Y_Private_Updates::enabled());
putenv('GRID_A11Y_GITHUB_TOKEN=test-credential-not-real');ok('Server environment credential is accepted',GRID_A11Y_Private_Updates::enabled());
putenv("GRID_A11Y_GITHUB_TOKEN=bad\r\nheader");ok('Header injection rejected',GRID_A11Y_Private_Updates::token()==='');
putenv('GRID_A11Y_GITHUB_TOKEN');
ok('Stable release accepted',GRID_A11Y_Private_Updates::stable_release('1.10.0',(object)['draft'=>false,'prerelease'=>false,'assets'=>[(object)['name'=>'grid-accessibility-toolbar-1.10.0.zip']]]));
ok('Wrong-version ZIP excluded',!GRID_A11Y_Private_Updates::stable_release('1.10.0',(object)['assets'=>[(object)['name'=>'grid-accessibility-toolbar-1.9.0.zip']]]));
ok('Draft excluded',!GRID_A11Y_Private_Updates::stable_release('1.10.0',(object)['draft'=>true]));
ok('Prerelease excluded',!GRID_A11Y_Private_Updates::stable_release('1.11.0',(object)['prerelease'=>true]));
ok('Development version excluded',!GRID_A11Y_Private_Updates::stable_release('1.11.0-beta',(object)[]));
ok('Branches and tags removed',array_keys(GRID_A11Y_Private_Updates::release_only(['latest_release'=>'r','latest_tag'=>'t','branch'=>'b']))===['latest_release']);
$url='https://api.github.com/repos/grid-arendal/grid-accessibility-toolbar/releases/assets/123';
ok('Owned repository asset accepted',GRID_A11Y_Private_Updates::valid_package((object)['version'=>'1.10.0','download_url'=>$url])!==null);
foreach([$url.'?access_token=secret',str_replace('api.github.com','evil.example',$url),str_replace('/grid-arendal/','/someone-else/',$url),str_replace('https:','http:',$url),'https://api.github.com/repos/grid-arendal/grid-accessibility-toolbar/zipball/main'] as $bad){ok('Unsafe or nonrelease package rejected',GRID_A11Y_Private_Updates::valid_package((object)['version'=>'1.10.0','download_url'=>$bad])===null);}
class MockGitHub extends \YahnisElsts\PluginUpdateChecker\v6p0\Vcs\GitHubApi {
 public $fixture;
 protected function api($url,$queryParams=array()){return $this->fixture;}
}
$api=new MockGitHub(GRID_A11Y_Private_Updates::REPOSITORY,'test-credential-not-real');
$api->enableReleaseAssets('/^grid-accessibility-toolbar-\d+\.\d+\.\d+\.zip$/D',\YahnisElsts\PluginUpdateChecker\v6p0\Vcs\Api::REQUIRE_RELEASE_ASSETS);
$api->setReleaseFilter([GRID_A11Y_Private_Updates::class,'stable_release'],\YahnisElsts\PluginUpdateChecker\v6p0\Vcs\Api::RELEASE_FILTER_SKIP_PRERELEASE,1);
$api->fixture=(object)['tag_name'=>'v1.10.0','draft'=>false,'prerelease'=>false,'zipball_url'=>'https://example.invalid/source.zip','created_at'=>'2026-11-13T00:00:00Z','assets'=>[]];
ok('No asset never falls back to source ZIP',$api->getLatestRelease()===null);
$api->fixture->assets=[(object)['name'=>'grid-accessibility-toolbar-1.10.0.zip','url'=>$url,'browser_download_url'=>'https://example.invalid/private.zip','download_count'=>0]];
$reference=$api->getLatestRelease();ok('Private download uses stable API asset endpoint',$reference->downloadUrl===$url);
$args=$api->filterUpdateDownloadRequestArgs(['headers'=>[]],$url);ok('Owned asset download uses header authentication',isset($args['headers']['Authorization'])&&$args['headers']['Accept']==='application/octet-stream');
$foreign=$api->filterUpdateDownloadRequestArgs(['headers'=>[]],'https://other.example/download.zip');ok('Other hosts get no credential',!isset($foreign['headers']['Authorization']));
$headers=$args['headers'];$location='https://release-assets.githubusercontent.com/signed.zip';$api->removeAuthHeaderFromRedirects($location,$headers);ok('GitHub CDN redirects strip credentials',!isset($headers['Authorization']));
define('GRID_A11Y_ENABLE_PRIVATE_UPDATES',false);ok('Explicit server opt-out disables updates',!GRID_A11Y_Private_Updates::enabled());
echo 'TOTAL '.$count."\n";
