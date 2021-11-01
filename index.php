<?php
  // required for login flow
  header('X-WP-Nonce: ' . wp_create_nonce('wpa_passwordless_login_request'));
?>

<pre>

    departure neuaubing, ns-dokumentationszentrum münchen

    coming soon

    <?php echo wp_registration_url(); ?>
</pre>

<?php wp_register(); ?>
