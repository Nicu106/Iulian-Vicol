#!/usr/bin/env bash
# Puts ivmotorclass.com on the v2 application. Run as root: bash deploy/cutover.sh
# 1 backup · 2 latest production data into v2 (keeping what only v2 has) · 3 photos
# 4 .env for the domain · 5 nginx · 6 checks. The old site is not modified: rollback.sh.
set -euo pipefail
V2=/var/www/motorclass-v2; OLD=/var/www/motorclass
B=/root/launch-backup-$(date +%Y%m%d-%H%M%S); mkdir -p "$B"
echo "== 1 backup → $B"
cp -a $OLD/database/database.sqlite "$B/prod-database.sqlite"
cp -a $V2/database/database.sqlite "$B/v2-database.sqlite"
cp -a $OLD/.env "$B/prod.env"; cp -a $V2/.env "$B/v2.env"
cp -a /etc/nginx/sites-available/motorclass "$B/nginx-motorclass"
cp -a /etc/nginx/sites-available/motorclass-v2 "$B/nginx-motorclass-v2"
sqlite3 "$B/prod-database.sqlite" "pragma integrity_check;" | grep -qx ok

echo "== 2 data: production rows, v2-only tables and columns kept"
systemctl stop motorclass-v2-queue
NEW=$V2/database/database.sqlite.new
cp "$B/prod-database.sqlite" "$NEW"
( cd $V2 && DB_DATABASE=$NEW php artisan migrate --force )
sqlite3 "$NEW" <<SQL
ATTACH '$B/v2-database.sqlite' AS v2;
DELETE FROM referral_events; DELETE FROM referral_visitors; DELETE FROM referral_rewards; DELETE FROM referrers;
INSERT INTO referrers SELECT * FROM v2.referrers;
INSERT INTO referral_rewards SELECT * FROM v2.referral_rewards;
INSERT INTO referral_visitors SELECT * FROM v2.referral_visitors;
INSERT INTO referral_events SELECT * FROM v2.referral_events;
UPDATE vehicles SET image_tags = (SELECT o.image_tags FROM v2.vehicles o WHERE o.slug = vehicles.slug)
  WHERE (image_tags IS NULL OR image_tags IN ('','[]','{}')) AND EXISTS (SELECT 1 FROM v2.vehicles o WHERE o.slug = vehicles.slug AND o.image_tags IS NOT NULL);
UPDATE vehicles SET referred_by = (SELECT o.referred_by FROM v2.vehicles o WHERE o.slug = vehicles.slug)
  WHERE referred_by IS NULL AND EXISTS (SELECT 1 FROM v2.vehicles o WHERE o.slug = vehicles.slug AND o.referred_by IS NOT NULL);
DETACH v2;
SQL
chown www-data:www-data "$NEW"; mv "$NEW" $V2/database/database.sqlite

echo "== 3 photos (new uploads since the last sync)"
rsync -a --exclude=cache/ $OLD/storage/app/public/ $V2/storage/app/public/
chown -R www-data:www-data $V2/storage/app/public

echo "== 4 .env for the domain"
sed -i -e 's#^APP_URL=.*#APP_URL=https://ivmotorclass.com#' -e 's#^APP_ENV=.*#APP_ENV=production#' \
       -e 's#^APP_DEBUG=.*#APP_DEBUG=false#' -e 's#^BRANDBOOK_ONLY=.*#BRANDBOOK_ONLY=false#' $V2/.env
( cd $V2 && php artisan config:clear && php artisan view:clear && php artisan route:clear )
systemctl start motorclass-v2-queue
# the daily check was for the design copy; it would now report false failures
systemctl disable --now motorclass-v2-check.timer || true

echo "== 5 nginx"
cp $V2/deploy/nginx-ivmotorclass.conf /etc/nginx/sites-available/ivmotorclass
cp $V2/deploy/nginx-v2design-redirect.conf /etc/nginx/sites-available/motorclass-v2-redirect
ln -sf /etc/nginx/sites-available/ivmotorclass /etc/nginx/sites-enabled/ivmotorclass
ln -sf /etc/nginx/sites-available/motorclass-v2-redirect /etc/nginx/sites-enabled/motorclass-v2-redirect
rm -f /etc/nginx/sites-enabled/motorclass /etc/nginx/sites-enabled/motorclass-v2
nginx -t && systemctl reload nginx

echo "== 6 checks"
R="--resolve ivmotorclass.com:443:127.0.0.1"
for p in / /catalogo /contacto /vende /recomienda /login /sitemap.xml /robots.txt; do
  printf '%-14s %s\n' "$p" "$(curl -s -o /dev/null -w '%{http_code}' $R https://ivmotorclass.com$p)"; done
printf '%-14s %s\n' "/catalog" "$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' $R https://ivmotorclass.com/catalog)"
printf '%-14s %s\n' "v2design" "$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' --resolve v2design.ivmotorclass.com:443:127.0.0.1 https://v2design.ivmotorclass.com/catalogo)"
systemctl start motorclass-v2-images.service || true
echo "== done. Backup: $B · rollback: bash $V2/deploy/rollback.sh"
