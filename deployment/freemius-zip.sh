#!/bin/zsh

mkdir -p freemius-plugin-pro

pushd ~/dev/PhpstormProjects/WGACT/
# zip src recursively without adding hidden files
zip -FSrq ~/dev/PhpstormProjects/WGACT/deployment/freemius-plugin-pro/plugin-freemius-deployment-unstripped.zip ./src/ -X -x "*/\.*"
popd