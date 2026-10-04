#!/usr/bin/env bash
# Puts ivmotorclass.com back on the old application (/var/www/motorclass), as before the launch.
set -euo pipefail
ln -sf /etc/nginx/sites-available/motorclass /etc/nginx/sites-enabled/motorclass
ln -sf /etc/nginx/sites-available/motorclass-v2 /etc/nginx/sites-enabled/motorclass-v2
rm -f /etc/nginx/sites-enabled/ivmotorclass /etc/nginx/sites-enabled/motorclass-v2-redirect
nginx -t && systemctl reload nginx
echo "ivmotorclass.com is on the old site again. Anything written to the v2 database since the launch stays in /var/www/motorclass-v2/database/database.sqlite."
