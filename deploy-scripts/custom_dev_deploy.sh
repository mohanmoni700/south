#!/bin/bash

############################################################
# Corratech (c) 2017 - 2021
############################################################

echo "Initiating Manual Build from deploy_dev custom pipeline"
echo " "
echo "Running from directory $(pwd)"
echo " "
echo "Build number: ${BITBUCKET_BUILD_NUMBER}"
echo " "

# Checking if the BITBUCKET_BRANCH variable is empty
if [ -z ${BITBUCKET_BRANCH} ]; then
       BITBUCKET_BRANCH=$1
fi

###############################################################
# CORRA : Prevent Production Deployment from unauthorized users
###############################################################

if [ "$BITBUCKET_BRANCH" = "production" ]; then
DEPLOY_TRIGGERER_UUID=$(echo ${BITBUCKET_STEP_TRIGGERER_UUID} | tr -d '{}')
if [[ " ${PROD_UUID_RBACL[@]} " =~ " ${DEPLOY_TRIGGERER_UUID} " ]]; then
    echo "200 - Deployment Access Allowed"
else
    echo "403 - Deployment Access Denied"
    exit 120
fi
fi


REMOTE_GIT_URL=${REMOTE_GIT_URL}
# Deployment Branch assignment from Bitbucket pipeline environment variables.
case "${BITBUCKET_BRANCH}" in
"environment/staging")
echo "Setting Stage details"
REMOTE_BRANCH=${REMOTE_BRANCH_STAGE}
REMOTE_REPO=${REMOTE_REPO_STAGE}
INSTANCE="Stage"
;;
"environment/preprod")
echo "Setting Preprod details"
REMOTE_BRANCH=${REMOTE_BRANCH_PREPROD}
REMOTE_REPO=${REMOTE_REPO_PREPROD}
INSTANCE="Preprod"
;;
"production")
echo "Setting Prod details"
REMOTE_BRANCH=${REMOTE_BRANCH_PROD}
REMOTE_REPO=${REMOTE_REPO_PROD}
INSTANCE="Prod"
;;
esac

### MAGENTO CODE DEPLOYMENT #####
echo "Adding a GIT Remote for ${INSTANCE} Deployment"
echo "Executing git remote add ${REMOTE_REPO} ${REMOTE_GIT_URL}"
git remote add ${REMOTE_REPO} ${REMOTE_GIT_URL}
echo ""
echo "List all active remotes "
git remote show
echo " "
echo "Starting Deployment to ${INSTANCE} Server "
echo "Check Git Log"
git log --oneline | head
echo "Executing git push ${REMOTE_REPO} HEAD:${REMOTE_BRANCH} -f"
git push -v ${REMOTE_REPO} HEAD:${REMOTE_BRANCH} -f &
echo "Please check Magento Cloud panel for Deployment completion status"
sleep 10
exit 0
