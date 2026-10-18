<?php
if(!defined('WP_CLI') || !WP_CLI)throw new RuntimeException('CLI only');
if(!class_exists('Instapass_Notifications') || !function_exists('as_get_scheduled_actions')){
    WP_CLI::error('Instapass notifications or Action Scheduler is unavailable.');
}
$pending=as_get_scheduled_actions(['group'=>Instapass_Notifications::GROUP,'status'=>'pending','per_page'=>1],'ids');
if(!$pending){WP_CLI::success('No stock-alert jobs pending.');return;}
// The group's existence is now proven. Never run another group's jobs.
WP_CLI::runcommand('action-scheduler run --group=instapass-notifications --batch-size=5 --batches=1',['launch'=>false]);
