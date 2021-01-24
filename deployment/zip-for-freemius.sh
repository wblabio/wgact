mkdir -p freemius

pushd ~/dev/PhpstormProjects/WGACT/
zip -r ~/dev/PhpstormProjects/WGACT/deployment/freemius/plugin-freemius-deployment.zip ./trunk/ -X -x "*/\.*"
popd