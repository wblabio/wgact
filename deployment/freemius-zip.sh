#!/bin/zsh

mkdir -p freemius

pushd ~/dev/PhpstormProjects/WGACT/
# zip trunk recursively without adding hidden files
zip -r ~/dev/PhpstormProjects/WGACT/deployment/freemius/plugin-freemius-deployment.zip ./trunk/ -X -x "*/\.*"
popd