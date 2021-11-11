release: rsync -avr --exclude='departure-neuaubing-api' --exclude='.gitignore' --exclude='Procfile' --exclude='README.md' --exclude='composer.*' --exclude='vendor' --exclude='.heroku' --exclude='.composer' --exclude='.release' --exclude='.profile.d' --exclude='.env' --exclude="*.json" ./ ./departure-neuaubing-api
web: vendor/bin/heroku-php-nginx
