<?php
$message = __('Someone requested that the password be reset for the following account:', 'h1-lost-password') . "\r\n\r\n";

$message .= network_home_url( '/' ) . "\r\n\r\n";

$message .= sprintf(__('Username: %s', 'h1-lost-password'), $user_login) . "\r\n\r\n";

$message .= __('If this was a mistake, just ignore this email and nothing will happen.', 'h1-lost-password') . "\r\n\r\n";

$message .= __('To reset your password, visit the following address:', 'h1-lost-password') . "\r\n\r\n";

$message .= '<' . network_site_url("lost-password?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";