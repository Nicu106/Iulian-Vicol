# Deployment Guide

## Quick Deploy

To deploy your changes to the production server, simply run:

```bash
./deploy.sh "Your commit message here"
```

## What the Script Does

The deployment script automates the entire deployment process:

### 1. Local Git Operations
- ✅ Checks for changes in your working directory
- ✅ Stages all changes (`git add .`)
- ✅ Commits with your provided message
- ✅ Pushes to GitHub (`main` branch)

### 2. Server Deployment
- ✅ SSHs into the production server (213.199.39.241)
- ✅ Navigates to `/var/www/motorclass`
- ✅ Pulls latest changes as `www-data` user
- ✅ Runs `npm install` (only if package.json changed)
- ✅ Restarts nginx service

## Examples

```bash
# Deploy with a simple message
./deploy.sh "Fixed login bug"

# Deploy with a detailed message
./deploy.sh "Added new vehicle listing feature with filters"

# Deploy configuration changes
./deploy.sh "Updated nginx configuration and security headers"
```

## Server Configuration

- **Host**: 213.199.39.241
- **User**: root
- **Project Path**: /var/www/motorclass
- **Branch**: main
- **Web Server**: nginx
- **PHP User**: www-data

## Automatic Features

✅ **Smart NPM Install**: The script detects if `package.json` has changed and only runs `npm install` when needed

✅ **Error Handling**: The script stops if any step fails (commit, push, pull, or nginx restart)

✅ **Color-Coded Output**: Easy to see success (green), warnings (yellow), errors (red), and info (blue)

✅ **Deployment Summary**: Shows a complete summary of what was deployed

## Prerequisites

1. SSH access to the server (make sure your SSH key is configured)
2. Git remote configured for the repository
3. Proper permissions on the server

## Testing SSH Connection

Before deploying, you can test your SSH connection:

```bash
ssh root@213.199.39.241 "cd /var/www/motorclass && pwd"
```

## Troubleshooting

### SSH Connection Issues
If you get SSH errors, ensure your SSH key is added:
```bash
ssh-add ~/.ssh/id_rsa  # or your key path
```

### Permission Issues on Server
If git pull fails with permission errors:
```bash
ssh root@213.199.39.241
cd /var/www/motorclass
sudo chown -R www-data:www-data .
```

### Nginx Won't Restart
Check nginx configuration:
```bash
ssh root@213.199.39.241
sudo nginx -t
```

## Manual Deployment (Fallback)

If the script fails, you can deploy manually:

```bash
# Local
git add .
git commit -m "Your message"
git push origin main

# On server
ssh root@213.199.39.241
cd /var/www/motorclass
sudo -u www-data git pull
sudo -u www-data npm install  # if needed
sudo systemctl restart nginx
```

## Security Note

The script uses SSH with root user. For production environments, consider:
- Using a dedicated deployment user instead of root
- Setting up SSH key-based authentication (no password)
- Adding the server to `~/.ssh/known_hosts` to avoid prompts

