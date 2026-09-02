#!/usr/bin/env bash
# Limpia todo lo que puede impedir que un cambio se vea en v2design.
#
#   ./refresh.sh
#
# Para editar CSS, JS o plantillas Blade NO hace falta ejecutarlo:
#   - los assets llevan ?v=<fecha de modificación> (directiva @assetv)
#   - nginx envía Cache-Control: no-cache en css/js/imágenes
#   - OPcache revalida en cada petición (opcache.revalidate_freq=0)
# Sirve para cuando tocas config/, routes/ o instalas algo.
set -e
cd "$(dirname "$0")"

php artisan view:clear   >/dev/null && echo "  vistas compiladas    limpiadas"
php artisan config:clear >/dev/null && echo "  caché de config      limpiada"
php artisan route:clear  >/dev/null && echo "  caché de rutas       limpiada"
php artisan cache:clear  >/dev/null 2>&1 && echo "  caché de aplicación  limpiada" || true
rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php

if [ "$(id -u)" = "0" ]; then
  systemctl reload php8.3-fpm && echo "  OPcache              vaciada"
else
  echo "  OPcache              no hace falta (revalida en cada petición)"
  echo "                       si aun así la quieres vaciar: sudo systemctl reload php8.3-fpm"
fi

echo
echo "  Listo. En el navegador basta con recargar normal (Ctrl+R)."
echo
./dev-check.sh
