#!/bin/sh
cd ..
mv $TRAVIS_BUILD_DIR $TRAVIS_BUILD_DIR/../Ongr/
mkdir $TRAVIS_BUILD_DIR
cd $TRAVIS_BUILD_DIR/../Ongr/
git clone https://github.com/ongr-io/ConnectionsBundle.git TmpConnectionsBundle
cd TmpConnectionsBundle
git checkout 43df7f8fbaacd47e63d17dc5b2aad6bc10857413
cd ..
