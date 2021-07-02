#!/bin/zsh

pushd () {
    command pushd "$@" > /dev/null
}

popd () {
    command popd "$@" > /dev/null
}

echo 'uninstalling current plugin from ~/dev/apps/wordpress-deployment/'
# https://stackoverflow.com/a/10372685/4688612
pushd ~/dev/apps/wordpress-deployment/wp-content/plugins/
wp plugin deactivate woocommerce-google-adwords-conversion-tracking-tag
# Detect if the plugin is symlinked. If so, delete the symlink. Otherwise, uninstall the plugin.
if ls -l | grep -q '^l.*woocommerce-google-adwords-conversion-tracking-tag ';
  then
    echo 'symlink detected -> removing symlink'
    rm woocommerce-google-adwords-conversion-tracking-tag
  else
    echo 'no symlink detected -> uninstalling plugin'
    wp plugin uninstall woocommerce-google-adwords-conversion-tracking-tag
fi
popd
echo 'unzipping '$1
mkdir -p tmp
unzip -qo $1 -d ~/dev/PhpstormProjects/WGACT/deployment/tmp
rm -rf ~/dev/PhpstormProjects/WGACT/deployment/wp.org/wgact/trunk/
mkdir ~/dev/PhpstormProjects/WGACT/deployment/wp.org/wgact/trunk/
cp -r ~/dev/PhpstormProjects/WGACT/deployment/tmp/woocommerce-google-adwords-conversion-tracking-tag/* ~/dev/PhpstormProjects/WGACT/deployment/wp.org/wgact/trunk/
rm -rf ~/dev/PhpstormProjects/WGACT/deployment/tmp/woocommerce-google-adwords-conversion-tracking-tag
# rmdir ~/dev/PhpstormProjects/WGACT/deployment/tmp
echo 'symlinking the new folder into the the deployment testing WordPress install'
pushd ~/dev/apps/wordpress-deployment/wp-content/plugins/
ln -s ~/dev/PhpstormProjects/WGACT/deployment/wp.org/wgact/trunk woocommerce-google-adwords-conversion-tracking-tag
popd
echo 'activating the plugin within the deployment WordPress install'
pushd ~/dev/apps/wordpress-deployment/
wp plugin activate woocommerce-google-adwords-conversion-tracking-tag
popd