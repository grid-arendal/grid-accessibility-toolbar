<?php
require __DIR__ . '/php-unit.php';
$a=GRID_Accessibility_Toolbar::sanitize(array('brand_name'=>'<b>Blue Carbon Kenya Hub</b>','brand_url'=>'javascript:alert(1)','brand_logo'=>'data:image/svg+xml,bad','surface_color'=>'red','color'=>'#005b56','brand_logo_layout'=>'wide','brand_logo_background'=>'accent','brand_credit'=>1,'panel_font'=>'site'));
verify('Brand name sanitised', $a['brand_name']==='Blue Carbon Kenya Hub');
verify('Unsafe branding URLs rejected', $a['brand_url']==='' && $a['brand_logo']==='');
verify('Invalid background replaced with safe default', $a['surface_color']==='#ffffff');
verify('Layout and website font saved', $a['brand_logo_layout']==='wide' && $a['brand_logo_background']==='accent' && $a['panel_font']==='site');
$a=GRID_Accessibility_Toolbar::sanitize(array('brand_name'=>array(),'brand_logo'=>array(),'brand_url'=>array(),'surface_color'=>array(),'panel_font'=>array(),'brand_logo_layout'=>array(),'brand_logo_background'=>array()));
verify('Malformed branding values fall back safely', $a['brand_name']==='GRID-Arendal' && $a['brand_logo']==='' && $a['panel_font']==='geologica');
verify('Credit can be disabled', $a['brand_credit']===0);
$a=GRID_Accessibility_Toolbar::branding(array_merge(GRID_Accessibility_Toolbar::defaults(), array('brand_name'=>'BCK Hub')));
verify('Custom brand never falls back to GRID logo', $a['logo']==='');
echo 'BRANDING TOTAL '.$count;
