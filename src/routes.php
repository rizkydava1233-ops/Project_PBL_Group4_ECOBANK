<?php
// Tabel rute: nama aksi (?a=...) => fungsi handler.
// Handler dipanggil dengan ($in, $id): body JSON dan id (int) dari body.

foreach (glob(__DIR__ . '/controllers/*.php') as $f) require $f;

return [
  'state'         => 'state_show',

  'login'         => 'auth_login',
  'register'      => 'auth_register',
  'logout'        => 'auth_logout',

  'pickup.create' => 'pickup_create',
  'pickup.cancel' => 'pickup_cancel',
  'pickup.take'   => 'pickup_take',
  'pickup.go'     => 'pickup_go',
  'pickup.done'   => 'pickup_done',

  'redeem'        => 'redeem_create',
  'redeem.fin'    => 'redeem_finish',

  'user.approve'  => 'user_approve',
  'user.delete'   => 'user_delete',
  'user.reset'    => 'user_reset',

  'reward.save'   => 'reward_save',
  'reward.stock'  => 'reward_stock',
  'reward.delete' => 'reward_delete',
  'rates.save'    => 'rates_save',

  'post.save'     => 'post_save',
  'post.delete'   => 'post_delete',

  'img'           => 'media_image',
  'export'        => 'report_export',
];
