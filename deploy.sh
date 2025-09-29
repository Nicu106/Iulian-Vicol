#!/bin/bash

# Script de deployment pentru Auto Ecommerce
# Rulează ca root sau cu sudo

echo "🚀 Deployment Auto Ecommerce - Pagină de Maintenance"
echo "=================================================="

# Variabile - Modifică după nevoie
PROJECT_DIR="/var/www/auto-ecommerce"
NGINX_SITES="/etc/nginx/sites-available"
NGINX_ENABLED="/etc/nginx/sites-enabled"
DOMAIN="auto-ecommerce.ro"

# Culori pentru output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funcție pentru logging
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verifică dacă rulează ca root
if [[ $EUID -ne 0 ]]; then
   error "Acest script trebuie rulat ca root (sudo)"
   exit 1
fi

# 1. Actualizează sistemul
log "Actualizez sistemul..."
apt update && apt upgrade -y

# 2. Instalează Nginx și PHP-FPM
log "Instalez Nginx și PHP-FPM..."
apt install -y nginx php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath

# 3. Creează directorul proiectului
log "Creez directorul proiectului..."
mkdir -p $PROJECT_DIR
chown -R www-data:www-data $PROJECT_DIR

# 4. Copiază fișierele
log "Copiez fișierele proiectului..."
# Presupunând că rulezi din directorul proiectului
cp -r * $PROJECT_DIR/
chown -R www-data:www-data $PROJECT_DIR

# 5. Configurează Nginx
log "Configurez Nginx..."
cp nginx.conf $NGINX_SITES/$DOMAIN

# Modifică configurația cu path-urile corecte
sed -i "s|/var/www/auto-ecommerce|$PROJECT_DIR|g" $NGINX_SITES/$DOMAIN

# Activează site-ul
ln -sf $NGINX_SITES/$DOMAIN $NGINX_ENABLED/

# Dezactivează site-ul default dacă există
if [ -f "$NGINX_ENABLED/default" ]; then
    rm $NGINX_ENABLED/default
    log "Site-ul default Nginx a fost dezactivat"
fi

# 6. Testează configurația Nginx
log "Testez configurația Nginx..."
nginx -t

if [ $? -eq 0 ]; then
    log "Configurația Nginx este validă"
    systemctl reload nginx
    log "Nginx a fost reîncărcat"
else
    error "Configurația Nginx are erori!"
    exit 1
fi

# 7. Pornește serviciile
log "Pornesc serviciile..."
systemctl enable nginx php8.2-fpm
systemctl start nginx php8.2-fpm

# 8. Configurează firewall-ul
log "Configurez firewall-ul..."
ufw allow 'Nginx Full'

# 9. Setează permisiunile corecte
log "Setez permisiunile..."
find $PROJECT_DIR -type f -exec chmod 644 {} \;
find $PROJECT_DIR -type d -exec chmod 755 {} \;
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache

# 10. Verifică statusul serviciilor
log "Verific statusul serviciilor..."
systemctl status nginx --no-pager -l
systemctl status php8.2-fpm --no-pager -l

# 11. Afișează informații finale
echo ""
echo "🎉 Deployment finalizat cu succes!"
echo "=================================="
echo "📁 Proiect instalat în: $PROJECT_DIR"
echo "🌐 Domain configurat: $DOMAIN"
echo "🔧 Configurație Nginx: $NGINX_SITES/$DOMAIN"
echo ""
echo "📝 Pași următori:"
echo "1. Configurează DNS-ul să pointeze către acest server"
echo "2. Pentru SSL: sudo certbot --nginx -d $DOMAIN"
echo "3. Pentru a activa aplicația Laravel, șterge /maintenance.html"
echo ""
echo "🔍 Verifică site-ul la: http://$DOMAIN"
echo ""

# Afișează IP-ul serverului
EXTERNAL_IP=$(curl -s ifconfig.me)
echo "📡 IP extern al serverului: $EXTERNAL_IP"
echo "💡 Dacă nu ai DNS configurat, poți testa la: http://$EXTERNAL_IP"



