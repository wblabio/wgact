#!/bin/zsh

pushd () {
    command pushd "$@" > /dev/null
}

popd () {
    command popd "$@" > /dev/null
}

echo 'uninstalling current plugin from deployment testing WordPress install'
pushd ~/dev/apps/wordpress-deployment/
wp plugin deactivate woocommerce-google-adwords-conversion-tracking-tag
wp plugin uninstall woocommerce-google-adwords-conversion-tracking-tag
popd
echo 'unzipping '$1
mkdir -p tmp
unzip -qo $1 -d ~/dev/PhpstormProjects/WGACT/deployment/tmp
cp -r ~/dev/PhpstormProjects/WGACT/deployment/tmp/woocommerce-google-adwords-conversion-tracking-tag/* ~/dev/PhpstormProjects/WGACT/deployment/wp.org/wgact/trunk/
# rmdir ~/dev/PhpstormProjects/WGACT/deployment/tmp/woocommerce-google-adwords-conversion-tracking-tag
# rmdir ~/dev/PhpstormProjects/WGACT/deployment/tmp
echo 'symlinking the new folder into the the deployment testing WordPress install'
pushd ~/dev/apps/wordpress-deployment/wp-content/plugins/
ln -s ~/dev/PhpstormProjects/WGACT/deployment/wp.org/wgact/trunk woocommerce-google-adwords-conversion-tracking-tag
popd
echo 'activating the plugin within the deployment WordPress install'
pushd ~/dev/apps/wordpress-deployment/
wp plugin activate woocommerce-google-adwords-conversion-tracking-tag
popd