<?php
if(!defined('ABSPATH'))exit;
final class GRID_Cookie_Receipts {
    public static function table(){global$wpdb;return$wpdb->prefix.'grid_cookie_receipts';}
    public static function policies(){global$wpdb;return$wpdb->prefix.'grid_cookie_policies';}
    public static function boot(){add_action('rest_api_init',array(__CLASS__,'routes'));add_action('grid_cookie_prune',array(__CLASS__,'prune'));add_action('admin_init',array(__CLASS__,'upgrade'));}
    public static function upgrade(){if(get_option('grid_cookie_db')!=='1'&&current_user_can('manage_options'))self::install();}
    public static function install(){global$wpdb;require_once ABSPATH.'wp-admin/includes/upgrade.php';$charset=$wpdb->get_charset_collate();$t=self::table();$p=self::policies();dbDelta("CREATE TABLE $t (id varchar(36) NOT NULL, policy varchar(20) NOT NULL, created bigint(20) unsigned NOT NULL, expires bigint(20) unsigned NOT NULL, choices text NOT NULL, method varchar(16) NOT NULL, language varchar(24) NOT NULL, gpc tinyint(1) NOT NULL DEFAULT 0, signature varchar(64) NOT NULL, PRIMARY KEY  (id), KEY expires (expires), KEY policy (policy)) $charset;");dbDelta("CREATE TABLE $p (revision varchar(20) NOT NULL, snapshot longtext NOT NULL, PRIMARY KEY  (revision)) $charset;");update_option('grid_cookie_db','1',false);}
    public static function origin(){
        $expected=wp_parse_url(home_url('/'));$origin=$_SERVER['HTTP_ORIGIN']??'';
        if($origin){$o=wp_parse_url($origin);return is_array($o)&&strtolower($o['scheme']??'')===strtolower($expected['scheme']??'')&&strtolower($o['host']??'')===strtolower($expected['host']??'')&&(int)($o['port']??(($o['scheme']??'')==='https'?443:80))===(int)($expected['port']??(($expected['scheme']??'')==='https'?443:80));}
        return($_SERVER['HTTP_SEC_FETCH_SITE']??'')==='same-origin';
    }
    public static function permission($request){return self::origin()?true:new WP_Error('origin','Only same-origin browser consent requests are accepted.',array('status'=>403));}
    public static function routes(){foreach(array('choice','verify')as$r)register_rest_route('grid-cookie/v1','/'.$r,array('methods'=>'POST','callback'=>array(__CLASS__,$r),'permission_callback'=>array(__CLASS__,'permission')));}
    public static function valid_id($v){return is_string($v)&&preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D',$v);}
    public static function normalize($data){if(!is_array($data)||!self::valid_id($data['id']??null)||!is_array($data['choices']??null))throw new InvalidArgumentException('Invalid consent data.');$choices=array();foreach(GRID_Cookie_Settings::categories()as$k){if(!array_key_exists($k,$data['choices'])||!is_bool($data['choices'][$k]))throw new InvalidArgumentException('Consent purposes must be explicit booleans.');$choices[$k]=$data['choices'][$k];}if(array_diff(array_keys($data['choices']),GRID_Cookie_Settings::categories()))throw new InvalidArgumentException('Unknown consent purpose.');return$choices;}
    public static function response($value,$status=200){$r=new WP_REST_Response($value,$status);$r->header('Cache-Control','private, no-store, max-age=0');$r->header('X-Content-Type-Options','nosniff');return$r;}
    public static function sign($state){return hash_hmac('sha256',wp_json_encode(array($state['id'],$state['revision'],$state['choices'],$state['created'],$state['expires'],$state['language'],$state['gpc'])),wp_salt('auth'));}
    public static function row_state($r){$s=array('id'=>$r['id'],'revision'=>$r['policy'],'choices'=>json_decode($r['choices'],true),'created'=>(int)$r['created'],'expires'=>(int)$r['expires'],'language'=>$r['language'],'gpc'=>(bool)$r['gpc']);$s['signature']=$r['signature'];return$s;}
    public static function choice($request){
        global$wpdb;if(strlen($request->get_body())>2048)return self::response(array('error'=>'Request too large'),413);
        try{$data=$request->get_json_params();$choices=self::normalize($data);}catch(Throwable$e){return self::response(array('error'=>$e->getMessage()),400);}
        $s=GRID_Cookie_Settings::get();$revision=GRID_Cookie_Settings::revision();if(($data['revision']??'')!==$revision)return self::response(array('error'=>'The notice changed. Reload and choose again.'),409);
        $gpc=($_SERVER['HTTP_SEC_GPC']??'')==='1'||!empty($data['gpc']);if($gpc)foreach($choices as$k=>$v)$choices[$k]=false;
        if(!$s['reviewed']&&in_array(true,$choices,true))return self::response(array('error'=>'Optional services are not enabled until the site setup is reviewed.'),409);
        $method=$data['method']??'';$lang=$data['language']??'';if(!in_array($method,array('accept','reject','save','withdraw','gpc'),true)||!is_string($lang)||!isset(GRID_Cookie_Settings::locales()[$lang]))return self::response(array('error'=>'Invalid action or language.'),400);
        if(in_array($method,array('reject','withdraw','gpc'),true)&&in_array(true,$choices,true))return self::response(array('error'=>'A denial cannot grant a purpose.'),400);
        $table=self::table();$old=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%s",$data['id']),ARRAY_A);
        if($old){$state=self::row_state($old);return $state['choices']===$choices&&$state['revision']===$revision?self::response($state):self::response(array('error'=>'Receipt IDs are immutable.'),409);}
        if((int)$wpdb->get_var("SELECT COUNT(*) FROM $table")>=100000)return self::response(array('error'=>'Receipt storage is full; optional services stay off.'),503);
        $now=time();$state=array('id'=>$data['id'],'revision'=>$revision,'choices'=>$choices,'created'=>$now,'expires'=>$now+$s['days']*DAY_IN_SECONDS,'language'=>$lang,'gpc'=>$gpc);$state['signature']=self::sign($state);
        $policy=self::policies();$snapshot=wp_json_encode(GRID_Cookie_Settings::snapshot());$wpdb->query($wpdb->prepare("INSERT IGNORE INTO $policy (revision,snapshot) VALUES (%s,%s)",$revision,$snapshot));
        $ok=$wpdb->insert($table,array('id'=>$state['id'],'policy'=>$revision,'created'=>$now,'expires'=>$state['expires'],'choices'=>wp_json_encode($choices),'method'=>$method,'language'=>$lang,'gpc'=>$gpc?1:0,'signature'=>$state['signature']),array('%s','%s','%d','%d','%s','%s','%s','%d','%s'));
        if(!$ok)return self::response(array('error'=>'Receipt could not be saved; optional services stay off.'),503);return self::response($state,201);
    }
    public static function verify($request){
        global$wpdb;if(strlen($request->get_body())>2048)return self::response(array('error'=>'Request too large'),413);$d=$request->get_json_params();try{$choices=self::normalize($d);}catch(Throwable$e){return self::response(array('error'=>'Invalid receipt'),400);}
        $table=self::table();$row=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%s",$d['id']),ARRAY_A);if(!$row)return self::response(array('error'=>'Unknown receipt'),403);$s=self::row_state($row);
        if($s['revision']!==GRID_Cookie_Settings::revision()||$s['expires']<=time()||$s['choices']!==$choices||!hash_equals(self::sign($s),$s['signature']))return self::response(array('error'=>'Expired, changed or invalid receipt'),403);
        if(empty($d['pending'])&&(!is_string($d['signature']??null)||!hash_equals($s['signature'],$d['signature'])))return self::response(array('error'=>'Invalid signature'),403);
        // Pending recovery is only for a freshly recorded decision during reload.
        if(!empty($d['pending'])&&time()-$s['created']>60)return self::response(array('error'=>'Recovery expired'),403);
        if(($_SERVER['HTTP_SEC_GPC']??'')==='1'&&in_array(true,$s['choices'],true))return self::response(array('error'=>'Global Privacy Control overrides optional consent'),403);
        return self::response($s);
    }
    public static function prune(){global$wpdb;$t=self::table();$p=self::policies();$limit=time()-GRID_Cookie_Settings::get()['retention']*DAY_IN_SECONDS;$wpdb->query($wpdb->prepare("DELETE FROM $t WHERE created<%d LIMIT 10000",$limit));$current=GRID_Cookie_Settings::revision();$wpdb->query($wpdb->prepare("DELETE FROM $p WHERE revision<>%s AND revision NOT IN (SELECT policy FROM $t)",$current));}
}
