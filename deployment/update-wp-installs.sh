pushd ~/dev/apps/wordpress
wp plugin update --all
wp theme update --all
wp language plugin update --all
popd

pushd ~/dev/apps/wordpress-deployment
wp plugin update --all
wp theme update --all
wp language plugin update --all
popd
