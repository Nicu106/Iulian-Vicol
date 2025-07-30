# Auto Premium - Site E-commerce Auto

Un site web profesional și modern pentru dealer auto, construit cu Laravel și Tailwind CSS.

## 🚀 Caracteristici

- **Design Modern și Minimalist**: Interfață curată cu paletă de culori neutră (alb, gri deschis, negru mat)
- **Responsive Design**: Optimizat pentru toate dispozitivele
- **Catalog Dinamic**: Filtrare și căutare avansată pentru vehicule
- **Galerie de Imagini**: Tranziții smooth și zoom la hover
- **Formulare Interactive**: Contact și cerere test-drive
- **Animații Smooth**: Efecte de scroll și micro-interacțiuni
- **SEO Optimizat**: Meta tags și structură semantică

## 🛠️ Tehnologii

- **Backend**: Laravel 12
- **Frontend**: Blade Templates + Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Assets**: Vite + PostCSS
- **Fonts**: Inter, Poppins (Google Fonts)

## 📋 Cerințe Sistem

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+ sau PostgreSQL 13+

## 🚀 Instalare

### 1. Clonează Repository-ul
```bash
git clone <repository-url>
cd auto-ecommerce
```

### 2. Instalează Dependențele PHP
```bash
composer install
```

### 3. Configurează Mediul
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurează Baza de Date
Editează fișierul `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auto_ecommerce
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Rulează Migrațiile și Seeder-ul
```bash
php artisan migrate
php artisan db:seed
```

### 6. Instalează Dependențele Node.js
```bash
npm install
```

### 7. Compilează Assets-urile
```bash
npm run build
```

### 8. Pornește Serverul
```bash
php artisan serve
```

Site-ul va fi disponibil la: `http://localhost:8000`

## 📁 Structura Proiectului

```
auto-ecommerce/
├── app/
│   ├── Http/Controllers/
│   │   ├── HomeController.php
│   │   ├── VehicleController.php
│   │   └── ContactController.php
│   └── Models/
│       ├── Vehicle.php
│       ├── VehicleImage.php
│       └── Contact.php
├── database/
│   ├── migrations/
│   │   ├── create_vehicles_table.php
│   │   ├── create_vehicle_images_table.php
│   │   └── create_contacts_table.php
│   └── seeders/
│       └── VehicleSeeder.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   ├── components/
│   │   │   ├── vehicle-card.blade.php
│   │   │   └── vehicle-card-small.blade.php
│   │   ├── vehicles/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── home.blade.php
│   │   ├── about.blade.php
│   │   ├── contact.blade.php
│   │   ├── faq.blade.php
│   │   └── terms.blade.php
│   ├── css/
│   │   └── app.css
│   └── js/
│       └── app.js
├── routes/
│   └── web.php
├── public/
│   └── build/
├── tailwind.config.js
├── postcss.config.js
└── vite.config.js
```

## 🎨 Design System

### Culori
- **Primar**: Gri (gray-800, gray-900)
- **Accent**: Galben (yellow-500, yellow-600)
- **Neutru**: Alb, gri deschis (gray-50, gray-100)

### Tipografie
- **Heading**: Poppins (600, 700)
- **Body**: Inter (400, 500)

### Componente
- **Butoane**: Stiluri primar, secundar, accent
- **Carduri**: Hover effects și shadow
- **Formulare**: Focus states și validare

## 📱 Pagini Disponibile

1. **Home** (`/`) - Pagina principală cu hero section și vehicule recomandate
2. **Catalog** (`/vehicles`) - Lista completă cu filtrare
3. **Detalii Vehicul** (`/vehicles/{slug}`) - Pagina detaliată cu galerie
4. **Despre Noi** (`/about`) - Informații despre companie
5. **Contact** (`/contact`) - Formular de contact și informații
6. **FAQ** (`/faq`) - Întrebări frecvente
7. **Termeni** (`/terms`) - Termeni și condiții

## 🔧 Configurare CMS

Pentru gestionarea conținutului, poți folosi:

### Opțiunea 1: Laravel Nova (Recomandat)
```bash
composer require laravel/nova
php artisan nova:install
```

### Opțiunea 2: Filament Admin
```bash
composer require filament/filament
php artisan filament:install
```

### Opțiunea 3: Custom Admin Panel
Creează un panel de administrare custom în `app/Http/Controllers/Admin/`

## 🚀 Deployment

### Pentru Producție
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Pentru Vercel/Netlify
Configurează build commands:
- **Build**: `npm run build && php artisan config:cache`
- **Output**: `public/`

## 📊 Date de Test

Proiectul include un seeder cu 6 vehicule de test:
- BMW X5 2023
- Mercedes-Benz C-Class 2022
- Audi A4 2021
- Volkswagen Golf 2023
- Toyota RAV4 2022
- Ford Focus 2021

## 🤝 Contribuții

1. Fork repository-ul
2. Creează un branch pentru feature (`git checkout -b feature/AmazingFeature`)
3. Commit schimbările (`git commit -m 'Add some AmazingFeature'`)
4. Push la branch (`git push origin feature/AmazingFeature`)
5. Deschide un Pull Request

## 📄 Licență

Acest proiect este licențiat sub MIT License.

## 📞 Suport

Pentru întrebări sau suport, contactează-ne la: support@autopremium.com

---

**Auto Premium** - Dealer Auto de Încredere 🚗✨
