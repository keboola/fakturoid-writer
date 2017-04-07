#!/bin/bash

docker login -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io
docker tag keboola/fakturoid-writer quay.io/keboola/fakturoid-writer:$TRAVIS_TAG
docker images
docker push quay.io/keboola/fakturoid-writer:$TRAVIS_TAG
