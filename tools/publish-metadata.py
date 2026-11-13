#!/usr/bin/env python3
"""Publish the public update feed only after verifying a completed stable release."""
import base64,json,os,re,subprocess
from pathlib import Path
repo=os.environ.get('GITHUB_REPOSITORY','')
if repo!='grid-arendal/grid-accessibility-toolbar':raise SystemExit('Unexpected repository')
data=json.loads(Path('dist/updates.json').read_text());version=data['version']
if not re.fullmatch(r'\d+\.\d+\.\d+',version):raise SystemExit('Not a stable version')
def api(endpoint):return subprocess.run(['gh','api',endpoint],text=True,capture_output=True)
r=api('repos/'+repo+'/releases/tags/v'+version)
if r.returncode:raise SystemExit('Cannot verify published release')
release=json.loads(r.stdout)
if release.get('draft') or release.get('prerelease'):raise SystemExit('Release is not stable and published')
name='grid-accessibility-toolbar-'+version+'.zip'
assets=[a for a in release.get('assets',[]) if a['name']==name and a.get('state')=='uploaded']
if len(assets)!=1 or assets[0]['browser_download_url']!=data['download_url']:raise SystemExit('Installable release asset missing or mismatched')
if assets[0].get('digest','').startswith('sha256:'):data['sha256']=assets[0]['digest'].split(':',1)[1]
old=api('repos/'+repo+'/contents/updates.json?ref=main');oldsha=None
if old.returncode==0:
 prior=json.loads(old.stdout);oldsha=prior['sha'];previous=json.loads(base64.b64decode(prior['content']))
 pv=previous.get('version','0.0.0')
 if re.fullmatch(r'\d+\.\d+\.\d+',pv) and tuple(map(int,pv.split('.')))>tuple(map(int,version.split('.'))):
  print('A newer update feed is already published; leaving it unchanged.');raise SystemExit(0)
elif '404' not in old.stderr:raise SystemExit('Cannot inspect existing update feed')
payload={'message':'Publish stable update feed '+version,'content':base64.b64encode((json.dumps(data,indent=2)+'\n').encode()).decode(),'branch':'main'}
if oldsha:payload['sha']=oldsha
write=subprocess.run(['gh','api','--method','PUT','repos/'+repo+'/contents/updates.json','--input','-'],input=json.dumps(payload),text=True,capture_output=True)
if write.returncode:raise SystemExit('Update feed publication failed; existing feed preserved')
print('Published public update feed for '+version)
