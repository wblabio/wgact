#!/bin/zsh

mkdir -p freemius-plugin-pro

pushd ~/dev/PhpstormProjects/WGACT/
# zip src recursively without adding hidden files
zip -r ~/dev/PhpstormProjects/WGACT/deployment/freemius-plugin-pro/plugin-freemius-deployment.zip ./trunk/ -X -x "*/\.*"
popd