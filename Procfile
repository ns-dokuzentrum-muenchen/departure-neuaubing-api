release: rsync -avr --exclude='departure-neuaubing-api' --exclude='.gitignore' --exclude='Procfile' --exclude='README.md' --exclude='composer.json' --exclude='composer.lock' --exclude='vendor' ./ ./departure-neuaubing-api
web: vendor/bin/heroku-php-nginx
