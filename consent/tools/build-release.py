#!/usr/bin/env python3
import argparse,pathlib,re,zipfile,hashlib,json
p=argparse.ArgumentParser();p.add_argument('--source',type=pathlib.Path,required=True);p.add_argument('--output',type=pathlib.Path,required=True);p.add_argument('--version',required=True);a=p.parse_args();r=a.source;v=a.version
assert re.fullmatch(r'\d+\.\d+\.\d+',v)
s=(r/'grid-cookie-consent.php').read_text();assert re.search(r'Version:\s*'+re.escape(v)+r'\s',s);assert "const VERSION = '"+v+"'"in s;assert 'Stable tag: '+v in(r/'readme.txt').read_text()
files=[]
for name in ['grid-cookie-consent.php','readme.txt','LICENSE.txt','THIRD-PARTY-NOTICES.txt','includes','assets']:
 q=r/name;assert q.exists();files.extend([q]if q.is_file()else sorted(x for x in q.rglob('*')if x.is_file()))
for f in files:
 assert not f.is_symlink()and not any(x.startswith('.')for x in f.relative_to(r).parts)
 assert not re.search(rb'github_pat_|ghp_[A-Za-z0-9]{20}|-----BEGIN .*PRIVATE KEY',f.read_bytes())
a.output.mkdir(parents=True,exist_ok=True);target=a.output/('grid-cookie-consent-'+v+'.zip')
with zipfile.ZipFile(target,'w',zipfile.ZIP_DEFLATED,compresslevel=9)as z:
 for f in files:z.write(f,'grid-cookie-consent/'+str(f.relative_to(r)))
with zipfile.ZipFile(target)as z:
 assert z.testzip()is None
 for f in files:assert z.read('grid-cookie-consent/'+str(f.relative_to(r)))==f.read_bytes()
h=hashlib.sha256(target.read_bytes()).hexdigest();(a.output/'SHA256SUMS.txt').write_text(h+'  '+target.name+'\n');meta={'name':'GRID Cookie Consent','slug':'grid-cookie-consent','version':v,'requires':'6.4','requires_php':'7.4','sha256':h,'download_url':'https://github.com/grid-arendal/grid-accessibility-toolbar/releases/download/consent-v'+v+'/'+target.name,'notes':(r/'RELEASE-NOTES.md').read_text()};(a.output/'consent-updates.json').write_text(json.dumps(meta,indent=2)+'\n');print(target.name,len(files),'files',h)
