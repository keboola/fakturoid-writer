#!/bin/bash

DP_VENDOR_ID="keboola"
DP_APP_ID="keboola.fakturoid-writer"

docker login -u="$QUAY_USERNAME" -p="$QUAY_PASSWORD" quay.io \
&& docker tag keboola/fakturoid-writer quay.io/keboola/fakturoid-writer:$TRAVIS_TAG \
&& docker images \
&& docker push quay.io/keboola/fakturoid-writer:$TRAVIS_TAG \
&& docker logout \
&& docker pull quay.io/keboola/developer-portal-cli-v2:latest \
&& export REPOSITORY=`docker run --rm \
  -e KBC_DEVELOPERPORTAL_USERNAME=$DP_USERNAME \
  -e KBC_DEVELOPERPORTAL_PASSWORD=$DP_PASSWORD \
  -e KBC_DEVELOPERPORTAL_URL=$DP_URL \
  quay.io/keboola/developer-portal-cli-v2:latest ecr:get-repository $DP_VENDOR_ID $DP_APP_ID` \
&& docker tag keboola/fakturoid-writer $REPOSITORY:$TRAVIS_TAG \
&& docker tag keboola/fakturoid-writer $REPOSITORY:latest \
&& docker images \
&& eval $(docker run --rm \
  -e KBC_DEVELOPERPORTAL_USERNAME=$DP_USERNAME \
  -e KBC_DEVELOPERPORTAL_PASSWORD=$DP_PASSWORD \
  -e KBC_DEVELOPERPORTAL_URL=$DP_URL \
  quay.io/keboola/developer-portal-cli-v2:latest ecr:get-login $DP_VENDOR_ID $DP_APP_ID) \
&& docker push $REPOSITORY:$TRAVIS_TAG \
&& docker push $REPOSITORY:latest \
&& docker run --rm \
  -e KBC_DEVELOPERPORTAL_USERNAME=$DP_USERNAME \
  -e KBC_DEVELOPERPORTAL_PASSWORD=$DP_PASSWORD \
  -e KBC_DEVELOPERPORTAL_URL=$DP_URL \
  quay.io/keboola/developer-portal-cli-v2:latest update-app-repository $DP_VENDOR_ID $DP_APP_ID $TRAVIS_TAG
