pushd ~/dev/apps/wordpress
wp core update
wp plugin update --all
wp theme update --all
wp language plugin update --all
popd

pushd ~/dev/apps/wordpress-deployment
wp core update
wp plugin update --all
wp theme update --all
wp language plugin update --all
popd
