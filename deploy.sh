#!/bin/bash

# Auto Ecommerce Deployment Script
# Automates git commit, push, and server deployment

# Server configuration
SERVER_HOST="213.199.39.241"
SERVER_USER="root"
PROJECT_PATH="/var/www/motorclass"
BRANCH="main"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log() {
    echo -e "${GREEN}[✓]${NC} $1"
}

info() {
    echo -e "${BLUE}[→]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

error() {
    echo -e "${RED}[✗]${NC} $1"
}

# Banner
echo ""
echo "╔═══════════════════════════════════════╗"
echo "║  Auto Ecommerce Deployment Script    ║"
echo "╚═══════════════════════════════════════╝"
echo ""

# Check for commit message argument
COMMIT_MESSAGE="$1"
if [ -z "$COMMIT_MESSAGE" ]; then
    error "Please provide a commit message"
    echo "Usage: ./deploy.sh \"Your commit message\""
    exit 1
fi

# Step 1: Check git status
info "Checking git status..."
if [ -n "$(git status --porcelain)" ]; then
    log "Changes detected"
else
    warning "No changes to commit"
    read -p "Continue with deployment anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 0
    fi
fi

# Step 2: Check if package.json has changed
PACKAGE_CHANGED=false
if git diff --name-only | grep -q "package.json"; then
    PACKAGE_CHANGED=true
    warning "package.json has changes - npm install will run on server"
elif git diff --cached --name-only | grep -q "package.json"; then
    PACKAGE_CHANGED=true
    warning "package.json has staged changes - npm install will run on server"
fi

# Step 3: Add all changes
info "Staging all changes..."
git add .
log "Changes staged"

# Step 4: Commit changes
info "Committing changes with message: '$COMMIT_MESSAGE'"
if git commit -m "$COMMIT_MESSAGE"; then
    log "Changes committed successfully"
else
    # Check if commit failed because there were no changes
    if [ $? -eq 1 ]; then
        warning "Nothing to commit (working tree clean)"
    else
        error "Commit failed"
        exit 1
    fi
fi

# Step 5: Push to GitHub
info "Pushing to GitHub ($BRANCH branch)..."
if git push origin $BRANCH; then
    log "Successfully pushed to GitHub"
else
    error "Failed to push to GitHub"
    exit 1
fi

echo ""
echo "╔═══════════════════════════════════════╗"
echo "║      Deploying to Server...           ║"
echo "╚═══════════════════════════════════════╝"
echo ""

# Step 6: SSH into server and deploy
info "Connecting to server $SERVER_HOST..."

# Create deployment commands
DEPLOY_COMMANDS="
cd $PROJECT_PATH || exit 1;
echo '→ Pulling latest changes...';
sudo -u www-data git stash;
sudo -u www-data GIT_SSH_COMMAND='ssh -i /var/www/.ssh/id_rsa' git pull origin $BRANCH;
if [ \$? -eq 0 ]; then
    echo '✓ Git pull successful';
else
    echo '✗ Git pull failed' >&2;
    exit 1;
fi;
# Ensure very high upload limits for 60+ images
echo '→ Configuring PHP-FPM upload limits...';
PHPV=\$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;');
UPLOAD_INI=/etc/php/\$PHPV/fpm/conf.d/99-upload_limits.ini;
printf "%s\n" \
  "file_uploads = On" \
  "max_file_uploads = 200" \
  "upload_max_filesize = 2048M" \
  "post_max_size = 2048M" \
  "memory_limit = 1024M" \
  "max_execution_time = 600" \
  "max_input_time = 600" \
  > \$UPLOAD_INI;
if [ \$? -eq 0 ]; then echo '✓ PHP limits file written at' \$UPLOAD_INI; else echo '✗ Failed to write PHP limits' >&2; fi;

echo '→ Configuring Nginx client_max_body_size...';
printf "%s\n" "client_max_body_size 2048M;" > /etc/nginx/conf.d/upload_limits.conf;
if [ \$? -eq 0 ]; then echo '✓ Nginx limits file written'; else echo '✗ Failed to write Nginx limits' >&2; fi;

echo '→ Restarting PHP-FPM...';
systemctl restart php\$PHPV-fpm || systemctl restart php-fpm;
if [ \$? -eq 0 ]; then echo '✓ PHP-FPM restarted'; else echo '✗ PHP-FPM restart failed' >&2; fi;
"

# Add npm install if package.json changed
if [ "$PACKAGE_CHANGED" = true ]; then
    DEPLOY_COMMANDS+="
echo '→ Running npm install...';
sudo -u www-data npm install;
if [ \$? -eq 0 ]; then
    echo '✓ npm install successful';
else
    echo '✗ npm install failed' >&2;
    exit 1;
fi;
"
fi

# Add nginx restart
DEPLOY_COMMANDS+="
echo '→ Restarting nginx...';
sudo systemctl restart nginx;
if [ \$? -eq 0 ]; then
    echo '✓ Nginx restarted successfully';
else
    echo '✗ Nginx restart failed' >&2;
    exit 1;
fi;
echo '';
echo '✓ Deployment completed successfully!';
"

# Execute deployment via SSH with password authentication
if sshpass -p "iulianiulianiuliansrv1" ssh -o StrictHostKeyChecking=no "$SERVER_USER@$SERVER_HOST" "$DEPLOY_COMMANDS"; then
    echo ""
    log "Deployment completed successfully!"
    echo ""
    echo "╔═══════════════════════════════════════╗"
    echo "║      Deployment Summary               ║"
    echo "╚═══════════════════════════════════════╝"
    echo "✓ Committed: $COMMIT_MESSAGE"
    echo "✓ Pushed to: GitHub ($BRANCH)"
    echo "✓ Deployed to: $SERVER_HOST"
    echo "✓ Project path: $PROJECT_PATH"
    if [ "$PACKAGE_CHANGED" = true ]; then
        echo "✓ npm packages updated"
    fi
    echo "✓ Nginx restarted"
    echo ""
else
    echo ""
    error "Deployment to server failed!"
    echo "Please check server connection and permissions"
    exit 1
fi
