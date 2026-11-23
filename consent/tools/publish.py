#!/usr/bin/env python3
import base64,json,os,subprocess,argparse
from pathlib import Path
p=argparse.ArgumentParser();p.add_argument('--metadata',type=Path,required=True);a=p.parse_args();repo=os.environ.get('GITHUB_REPOSITORY');assert repo=='grid-arendal/grid-accessibility-toolbar';d=json.loads(a.metadata.read_text());assert d['slug']=='grid-cookie-consent'
def call(path,*extra):return subprocess.run(['gh','api',path,*extra],text=True,capture_output=True)
r=call('repos/'+repo+'/releases/tags/consent-v'+d['version']);assert r.returncode==0,r.stderr;x=json.loads(r.stdout);assert not x['draft']and not x['prerelease'];asset=next(a for a in x['assets']if a['name']=='grid-cookie-consent-'+d['version']+'.zip');assert asset['browser_download_url']==d['download_url'];assert asset['digest']=='sha256:'+d['sha256']
path='repos/'+repo+'/contents/consent-updates.json';old=call(path+'?ref=main');payload={'message':'Publish consent update feed '+d['version'],'branch':'main','content':base64.b64encode((json.dumps(d,indent=2)+'\n').encode()).decode()}
if old.returncode==0:
 old=json.loads(old.stdout);previous=json.loads(base64.b64decode(old['content']));assert tuple(map(int,d['version'].split('.')))>=tuple(map(int,previous['version'].split('.')));payload['sha']=old['sha']
elif '404'not in old.stderr:raise SystemExit(old.stderr)
r=subprocess.run(['gh','api','--method','PUT',path,'--input','-'],input=json.dumps(payload),text=True,capture_output=True);assert r.returncode==0,r.stderr;print('Published independent consent feed; existing product feeds unchanged.')
