#!/bin/bash

# ============================================
# FIXED DEPLOYMENT SCRIPT - Auto Ecommerce
# ============================================
# This script GUARANTEES that changes reach the server

set -e  # Exit on any error

# Server configuration
SERVER_HOST="213.199.39.241"
SERVER_USER="root"
SERVER_PASS="iulianiulianiuliansrv1"
PROJECT_PATH="/var/www/motorclass"
BRANCH="main"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Logging functions
log_success() { echo -e "${GREEN}✓${NC} $1"; }
log_info() { echo -e "${BLUE}→${NC} $1"; }
log_warning() { echo -e "${YELLOW}!${NC} $1"; }
log_error() { echo -e "${RED}✗${NC} $1"; }

# Banner
echo ""
echo "╔════════════════════════════════════════╗"
echo "║   FIXED Auto Ecommerce Deployment     ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Check for commit message
COMMIT_MESSAGE="$1"
if [ -z "$COMMIT_MESSAGE" ]; then
    log_error "Please provide a commit message"
    echo "Usage: ./deploy-fixed.sh \"Your commit message\""
    exit 1
fi

# ============================================
# STEP 1: LOCAL GIT OPERATIONS
# ============================================
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  STEP 1: Local Git Operations"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

log_info "Checking git status..."
if [ -z "$(git status --porcelain)" ]; then
    log_warning "No changes detected"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 0
    fi
fi

log_info "Staging all changes..."
git add .
log_success "Changes staged"

log_info "Committing: '$COMMIT_MESSAGE'"
if git commit -m "$COMMIT_MESSAGE" 2>/dev/null; then
    log_success "Committed successfully"
else
    log_warning "Nothing to commit (already committed)"
fi

log_info "Pushing to GitHub ($BRANCH)..."
git push origin $BRANCH
log_success "Pushed to GitHub"

# Get current commit hash for verification
LOCAL_COMMIT=$(git rev-parse --short HEAD)
log_info "Local commit: $LOCAL_COMMIT"

# ============================================
# STEP 2: SERVER DEPLOYMENT
# ============================================
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  STEP 2: Server Deployment"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

log_info "Connecting to $SERVER_HOST..."

# Pull latest changes on server
log_info "Pulling latest changes on server..."
sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" << ENDSSH
set -e
cd $PROJECT_PATH

echo "→ Current commit on server:"
git log -1 --oneline

echo ""
echo "→ Pulling from GitHub..."
sudo -u www-data git pull origin $BRANCH

echo ""
echo "→ New commit on server:"
git log -1 --oneline

echo ""
echo "→ Restarting Nginx..."
systemctl restart nginx
echo "✓ Nginx restarted"

echo ""
echo "→ Verifying CSS file..."
ls -lh public/css/app.css

echo ""
echo "✓ Deployment completed on server!"
ENDSSH

if [ $? -eq 0 ]; then
    log_success "Server deployment successful"
else
    log_error "Server deployment failed"
    exit 1
fi

# ============================================
# STEP 3: VERIFICATION
# ============================================
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  STEP 3: Verification"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

log_info "Verifying deployment..."

SERVER_COMMIT=$(sshpass -p "$SERVER_PASS" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" "cd $PROJECT_PATH && git rev-parse --short HEAD")

echo ""
echo "Local commit:  $LOCAL_COMMIT"
echo "Server commit: $SERVER_COMMIT"
echo ""

if [ "$LOCAL_COMMIT" == "$SERVER_COMMIT" ]; then
    log_success "Commits match! Deployment verified ✓"
else
    log_error "Commits DO NOT match! Deployment may have failed"
    exit 1
fi

# ============================================
# DEPLOYMENT SUMMARY
# ============================================
echo ""
echo "╔════════════════════════════════════════╗"
echo "║         Deployment Summary             ║"
echo "╚════════════════════════════════════════╝"
echo "✓ Commit: $COMMIT_MESSAGE"
echo "✓ Hash: $LOCAL_COMMIT"
echo "✓ Pushed to: GitHub ($BRANCH)"
echo "✓ Deployed to: $SERVER_HOST"
echo "✓ Path: $PROJECT_PATH"
echo "✓ Nginx: Restarted"
echo "✓ Verified: Server matches local"
echo ""
log_success "Deployment completed successfully!"
echo ""
echo "🌐 Your changes are now LIVE!"
echo "💡 Clear browser cache to see changes: Ctrl+Shift+R"
echo ""
