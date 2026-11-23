<?php
if(!defined('ABSPATH'))exit;
final class GRID_Cookie_Blocker {
    public static function attrs($tag){$out=array();preg_match_all('/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*=\s*(?:"([^"]*)"|\x27([^\x27]*)\x27|([^\s>]+))/s',$tag,$m,PREG_SET_ORDER);foreach($m as$v)$out[strtolower($v[1])]=html_entity_decode(($v[2]??'')!==''?$v[2]:(($v[3]??'')!==''?$v[3]:($v[4]??'')),ENT_QUOTES|ENT_HTML5,'UTF-8');return$out;}
    public static function remove_attr($tag,$name){return preg_replace('/\s+'.preg_quote($name,'/').'\s*=\s*(?:"[^"]*"|\x27[^\x27]*\x27|[^\s>]+)/i','',$tag);}
    public static function tracker($text){
        if(preg_match('~(?:googletagmanager\.com|google-analytics\.com|analytics\.google\.com|\bgtag\s*\(|\bga\s*\(\s*[\x27"](?:create|send)|google_analytics|gaq\.push|gtm\.start|GTM-[A-Z0-9]+)~i',$text))return'google';
        if(preg_match('~clarity\.ms|clarity\s*\(\s*[\x27"]|[\x27"]clarity[\x27"]\s*,\s*[\x27"]script~i',$text))return'clarity';
        if(preg_match('~adsagentclient|brand-agent-frontend|connect\.facebook\.net|facebook\.com/tr|bat\.bing\.com|hotjar\.(?:com|io)|tiktok\.com/i18n~i',$text))return'other-tracker';
        return false;
    }
    public static function inert_script($open,$body,$category){
        $a=self::attrs($open);$type=$a['type']??'text/javascript';$open=self::remove_attr(self::remove_attr($open,'type'),'src');$open=rtrim(substr($open,0,-1));
        return $open.' type="text/plain" data-grid-consent="'.esc_attr($category).'" data-grid-cookie-type="'.esc_attr($type).'"'.(isset($a['src'])?' data-grid-cookie-src="'.esc_attr($a['src']).'"':'').'>'.$body.'</script>';
    }
    public static function filter($html){
        if(!is_string($html)||!preg_match('~<!doctype\s+html|<html\b~i',substr($html,0,2048)))return$html;
        $s=GRID_Cookie_Settings::get();
        $html=preg_replace_callback('~(<script\b[^>]*>)([\s\S]*?)</script\s*>~i',function($m)use($s){
            $a=self::attrs($m[1]);$id=$a['id']??'';
            if(in_array($id,array('grid-cookie-config','grid-cookie-defaults','grid-cookie-runtime'),true))return$m[0];
            $type=strtolower(trim($a['type']??''));if($type&& !in_array($type,array('text/javascript','application/javascript','module','text/plain'),true))return$m[0];
            if(isset($a['data-grid-consent'])){
                $cat=in_array($a['data-grid-consent'],GRID_Cookie_Settings::categories(),true)?$a['data-grid-consent']:'invalid';
                if($type==='text/plain'&&!isset($a['src']))return$m[0];
                return self::inert_script($m[1],$m[2],$cat);
            }
            $text=($a['src']??'').' '.$m[2];
            foreach($s['services']as$v)if(isset($a['src'])&&$a['src']===$v['src'])return'<!-- GRID consent: custom service is managed by the consent registry -->';
            if($kind=self::tracker($text))return'<!-- GRID consent blocked competing '.$kind.' snippet -->';
            if($s['functional']&&preg_match('~gtranslateSettings|cdn\.gtranslate\.net|translate\.google\.(?:com|[a-z.]+)/translate_a~i',$text))return self::inert_script($m[1],$m[2],'functional');
            return$m[0];
        },$html);
        $html=preg_replace_callback('~<iframe\b[^>]*>[\s\S]*?</iframe\s*>~i',function($m)use($s){$at=strpos($m[0],'>');$open=substr($m[0],0,$at+1);$a=self::attrs($open);$src=$a['src']??'';
            if(self::tracker($src))return'<!-- GRID consent blocked tracking iframe -->';
            if(($a['data-grid-cookie-essential']??'')==='true')return$m[0];
            $u=wp_parse_url($src);$host=$u['host']??'';$home=wp_parse_url(home_url('/'),PHP_URL_HOST);
            if(!$s['functional']||!$src||!$host||strtolower($host)===strtolower($home))return$m[0];
            // srcdoc can contain arbitrary trackers; never auto-replay it.
            $open=self::remove_attr(self::remove_attr($open,'src'),'srcdoc');
            return '<div class="grid-cookie-embed"><button type="button" class="grid-cookie-open">External content: open cookie settings</button>'.rtrim(substr($open,0,-1)).' src="about:blank" data-grid-cookie-frame="functional" data-grid-cookie-src="'.esc_attr($src).'" title="'.esc_attr($a['title']??'Optional external content').'">'.substr($m[0],$at+1).'</div>';
        },$html);
        $html=preg_replace_callback('~<link\b[^>]*>~i',function($m)use($s){$a=self::attrs($m[0]);$href=$a['href']??'';$rel=strtolower($a['rel']??'');$h=wp_parse_url($href,PHP_URL_HOST)?:'';
            $known=self::tracker($href)||preg_match('/(?:^|\.)(?:gtranslate\.net|fonts\.googleapis\.com|fonts\.gstatic\.com)$/i',$h);
            foreach($s['services']as$v)if($h&&(wp_parse_url($v['src'],PHP_URL_HOST)===$h))$known=true;
            if($known&&preg_match('/preconnect|dns-prefetch|preload|prefetch|modulepreload/',$rel))return'<!-- GRID consent blocked speculative connection -->';
            if($s['functional']&&$rel==='stylesheet'&&preg_match('/(?:^|\.)fonts\.googleapis\.com$/i',$h)){ $open=self::remove_attr($m[0],'href');return rtrim(substr($open,0,-1)).' data-grid-cookie-style="functional" data-grid-cookie-src="'.esc_attr($href).'">';}
            return$m[0];},$html);
        $html=preg_replace_callback('~<img\b[^>]*>~i',function($m){$a=self::attrs($m[0]);return self::tracker(($a['src']??'').' '.($a['srcset']??''))?'<!-- GRID consent blocked tracking pixel -->':$m[0];},$html);
        return$html;
    }
}
