#!/usr/bin/env python3
"""Build an installable ZIP using runtime files only. Requires Python 3.8+."""
import argparse, hashlib, re, zipfile
from pathlib import Path
parser=argparse.ArgumentParser()
parser.add_argument('--source',type=Path,default=Path(__file__).resolve().parents[1])
parser.add_argument('--output',type=Path,default=Path('dist'))
parser.add_argument('--version',required=True)
a=parser.parse_args();root=a.source.resolve();version=a.version
if not re.fullmatch(r'\d+\.\d+\.\d+',version):raise SystemExit('A stable x.y.z version is required')
main=(root/'grid-accessibility-toolbar.php').read_text()
for pattern in [r'\* Version:\s*([\d.]+)',r"const VERSION = '([\d.]+)'"]:
 match=re.search(pattern,main)
 if not match or match.group(1)!=version:raise SystemExit('Plugin version does not match release version')
if not re.search(r'^Stable tag:\s*'+re.escape(version)+r'\s*$',(root/'readme.txt').read_text(),re.M):raise SystemExit('Readme stable tag does not match release')
allowed=['grid-accessibility-toolbar.php','readme.txt','LICENSE.txt','THIRD-PARTY-NOTICES.txt','assets','includes','vendor']
files=[]
for name in allowed:
 p=root/name
 if not p.exists():raise SystemExit('Missing runtime path: '+name)
 files.extend([p] if p.is_file() else sorted(q for q in p.rglob('*') if q.is_file()))
for p in files:
 rel=p.relative_to(root)
 if p.is_symlink() or any(x.startswith('.') for x in rel.parts):raise SystemExit('Unexpected runtime path: '+str(rel))
 b=p.read_bytes()
 if re.search(rb'github_pat_[A-Za-z0-9_]{20,}|gh[pousr]_[A-Za-z0-9]{20,}|-----BEGIN (?:RSA |EC |OPENSSH )?PRIVATE KEY-----',b):raise SystemExit('Potential secret in: '+str(rel))
a.output.mkdir(parents=True,exist_ok=True);target=a.output/('grid-accessibility-toolbar-'+version+'.zip')
with zipfile.ZipFile(target,'w',zipfile.ZIP_DEFLATED,compresslevel=9) as z:
 for p in files:z.write(p,'grid-accessibility-toolbar/'+str(p.relative_to(root)))
with zipfile.ZipFile(target) as z:
 assert z.testzip() is None
 assert len(z.namelist())==len(files)
 for p in files:assert z.read('grid-accessibility-toolbar/'+str(p.relative_to(root)))==p.read_bytes()
digest=hashlib.sha256(target.read_bytes()).hexdigest()
(a.output/'SHA256SUMS.txt').write_text(digest+'  '+target.name+'\n')
print(target.name, 'files='+str(len(files)), 'sha256='+digest)
