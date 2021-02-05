#!/bin/zsh

php changelog-prep.php

cp tmp/changelog-pro.md ~/dev/apps/wblabio.github.io/wgact/changelog-pro.md
cp tmp/changelog-free.md ~/dev/apps/wblabio.github.io/wgact/changelog-free.md

pushd ~/dev/apps/wblabio.github.io/
git commit -am 'updated changelog'
git push
popd
