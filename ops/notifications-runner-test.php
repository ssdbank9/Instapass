<?php
if(getenv('INSTAPASS_ISOLATED_TEST')!=='yes')throw new RuntimeException('Isolated clone only');
add_filter('pre_wp_mail','__return_true',999);
$ids=as_get_scheduled_actions(['group'=>Instapass_Notifications::GROUP,'status'=>'pending','per_page'=>1000],'ids');
foreach($ids as $id)ActionScheduler::store()->cancel_action($id);
update_option('instapass_probe_ran',0);
update_option('instapass_other_probe_ran',0);
add_action('instapass_queue_probe',function(){update_option('instapass_probe_ran',1);});
add_action('instapass_other_probe',function(){update_option('instapass_other_probe_ran',1);});
as_schedule_single_action(time()-5,'instapass_queue_probe',[],Instapass_Notifications::GROUP);
$other=as_schedule_single_action(time()-10,'instapass_other_probe',[],'instapass-isolated-other');
require '/tmp/notifications-runner.php';
if(get_option('instapass_probe_ran')!=1 || get_option('instapass_other_probe_ran')!=0)throw new RuntimeException('Runner group boundary failed');
echo "PASS runner processes its due job and preserves other group.\n";
ActionScheduler::store()->cancel_action($other);
require '/tmp/notifications-runner.php';
echo "PASS runner exits cleanly with empty queue.\n";
