<?php
if(getenv('INSTAPASS_SEND_OWNER_TEST')!=='yes'){throw new RuntimeException('Set INSTAPASS_SEND_OWNER_TEST=yes only for an explicitly authorized owner test.');}
$sent=wc_mail('instapass0408@gmail.com','Instapass checkout verification 20261019-replyto','<p>This is your internal Instapass checkout verification message.</p><p>The guest checkout, coupon, order tracking and manual payment workflow has been updated. This test creates no order and requests no payment.</p><p><a href="https://instapass.online/track-order/">Open order tracking</a></p>');
echo wp_json_encode(array('transport_accepted'=>(bool)$sent));
