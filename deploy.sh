sudo -u www-data composer install
sudo -u www-data php bin/console asset:install
sudo -u www-data php bin/console cache:clear
sudo chown -R www-data:www-data ./
