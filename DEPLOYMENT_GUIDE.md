# 🚀 Ghid de Deployment - Auto Ecommerce

## 📋 Prezentare Generală

Acest ghid te va ajuta să pui online aplicația Auto Ecommerce cu o pagină de maintenance frumoasă până când aplicația principală va fi gata.

## 🛠️ Metoda 1: Deployment Automat (Recomandat)

### Prerequizite
- Server Ubuntu/Debian cu acces root
- Conexiune SSH la server

### Pași:

1. **Copiază fișierele pe server**
```bash
# Pe serverul tău, creează directorul
mkdir -p /tmp/auto-ecommerce

# Copiază fișierele (folosește SCP, SFTP sau Git)
scp -r * user@your-server:/tmp/auto-ecommerce/
```

2. **Rulează scriptul de deployment**
```bash
# Conectează-te la server
ssh user@your-server

# Navighează la directorul proiectului
cd /tmp/auto-ecommerce

# Rulează scriptul ca root
sudo ./deploy.sh
```

## 🔧 Metoda 2: Deployment Manual

### 1. Instalează Nginx și PHP

```bash
# Actualizează sistemul
sudo apt update && sudo apt upgrade -y

# Instalează Nginx și PHP-FPM
sudo apt install -y nginx php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-bcmath
```

### 2. Configurează Directorul Proiectului

```bash
# Creează directorul
sudo mkdir -p /var/www/auto-ecommerce

# Copiază fișierele
sudo cp -r * /var/www/auto-ecommerce/

# Setează permisiunile
sudo chown -R www-data:www-data /var/www/auto-ecommerce
sudo find /var/www/auto-ecommerce -type f -exec chmod 644 {} \;
sudo find /var/www/auto-ecommerce -type d -exec chmod 755 {} \;
```

### 3. Configurează Nginx

```bash
# Copiază configurația
sudo cp nginx.conf /etc/nginx/sites-available/auto-ecommerce.ro

# Activează site-ul
sudo ln -s /etc/nginx/sites-available/auto-ecommerce.ro /etc/nginx/sites-enabled/

# Dezactivează site-ul default
sudo rm -f /etc/nginx/sites-enabled/default

# Testează configurația
sudo nginx -t

# Reîncarcă Nginx
sudo systemctl reload nginx
```

### 4. Pornește Serviciile

```bash
# Activează serviciile
sudo systemctl enable nginx php8.2-fpm

# Pornește serviciile
sudo systemctl start nginx php8.2-fpm

# Verifică statusul
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
```

## 🌐 Metoda 3: Deployment cu Git (Pentru actualizări ușoare)

### 1. Pune proiectul pe GitHub (dacă nu ai făcut deja)

```bash
# În directorul local al proiectului
git init
git add .
git commit -m "Initial commit - Auto Ecommerce with maintenance page"
git remote add origin https://github.com/USERNAME/auto-ecommerce.git
git push -u origin main
```

### 2. Clone pe server

```bash
# Pe server
cd /var/www
sudo git clone https://github.com/USERNAME/auto-ecommerce.git
sudo chown -R www-data:www-data auto-ecommerce
```

### 3. Configurează Nginx (ca la metoda 2)

## 🔒 Configurarea SSL (Opțional dar Recomandat)

### Cu Let's Encrypt (Gratuit)

```bash
# Instalează Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obține certificatul SSL
sudo certbot --nginx -d auto-ecommerce.ro -d www.auto-ecommerce.ro

# Testează auto-renewal
sudo certbot renew --dry-run
```

## 📝 Configurarea DNS

Pointează domeniul către serverul tău:

```
Type: A
Name: @
Value: IP_ADDRESS_OF_YOUR_SERVER

Type: A  
Name: www
Value: IP_ADDRESS_OF_YOUR_SERVER
```

## 🔧 Configurări Post-Deployment

### 1. Pentru a activa aplicația Laravel mai târziu:

```bash
# Șterge pagina de maintenance
sudo rm /var/www/auto-ecommerce/public/maintenance.html

# Configurează .env pentru Laravel
sudo cp /var/www/auto-ecommerce/.env.example /var/www/auto-ecommerce/.env
sudo nano /var/www/auto-ecommerce/.env

# Generează cheia aplicației
cd /var/www/auto-ecommerce
sudo php artisan key:generate
sudo php artisan migrate
```

### 2. Monitorizare și Logs

```bash
# Logs Nginx
sudo tail -f /var/log/nginx/auto-ecommerce.access.log
sudo tail -f /var/log/nginx/auto-ecommerce.error.log

# Logs PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

## 🚨 Troubleshooting

### Probleme comune:

1. **403 Forbidden**
   - Verifică permisiunile: `sudo chown -R www-data:www-data /var/www/auto-ecommerce`

2. **502 Bad Gateway**
   - Verifică PHP-FPM: `sudo systemctl status php8.2-fpm`
   - Restart PHP-FPM: `sudo systemctl restart php8.2-fpm`

3. **Nginx nu pornește**
   - Testează config: `sudo nginx -t`
   - Verifică logs: `sudo journalctl -u nginx`

4. **Site nu se încarcă**
   - Verifică firewall: `sudo ufw status`
   - Permite trafic: `sudo ufw allow 'Nginx Full'`

## 📞 Suport

Pentru probleme sau întrebări, contactează echipa de dezvoltare.

---

**Data creării:** $(date)  
**Versiune:** 1.0  
**Ultima actualizare:** $(date)


