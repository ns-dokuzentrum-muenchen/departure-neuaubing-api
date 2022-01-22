<?php
  // required for login flow
  header('X-WP-Nonce: ' . wp_create_nonce('wpa_passwordless_login_request'));
?>
<!doctype html>
<html>
  <head>
    <title>Redirecting...</title>
    <meta http-equiv="refresh" content="3; URL=https://departure-neuaubing.nsdoku.de"/>
    <style>
      body { margin: 2em; font-family: monospace; }
      h1 { font-size: 1em; }
    </style>
  </head>
  <body>
    <h1>departure neuaubing, ns-dokumentationszentrum münchen</h1>

    <p>Redirecting to <a href="https://departure-neuaubing.nsdoku.de">departure-neuaubing.nsdoku.de</a></p>
  </body>
</html>
