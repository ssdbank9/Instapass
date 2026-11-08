import json, subprocess

def run(*args):
    return subprocess.check_output(args, text=True)

c = json.loads(run('docker', 'inspect', 'instapass-wordpress-1'))[0]
labels = c['Config']['Labels']
files = labels.get('com.docker.compose.project.config_files', '').split(',')
print(json.dumps({'image': c['Config']['Image'], 'image_id': c['Image'], 'mounts': [{'source':m['Source'], 'destination':m['Destination'], 'rw':m['RW']} for m in c['Mounts']], 'config_files': files, 'service': labels.get('com.docker.compose.service')}, indent=2))
args = ['docker', 'compose', '--project-directory', '/opt/instapass']
for f in files:
    if f: args += ['-f', f]
cfg = json.loads(run(*(args + ['config', '--format', 'json'])))
print(json.dumps({'services': {k: {'image':v.get('image'), 'volumes':v.get('volumes'), 'ports':v.get('ports')} for k,v in cfg['services'].items()}}, indent=2))
print(run('docker', 'exec', 'instapass-wordpress-1', 'php', '-r', 'require "/var/www/html/wp-includes/version.php"; echo "WordPress=".$wp_version." PHP=".PHP_VERSION." upload_progress=".ini_get("session.upload_progress.enabled").PHP_EOL;'))
print(run('docker', 'image', 'inspect', c['Image'], '--format', '{{json .RepoTags}}'))
