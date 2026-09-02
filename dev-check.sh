#!/usr/bin/env bash
# Comprueba que el entorno de diseño sigue configurado para NO acumular caché
# y que sigue aislado de producción. Sale con código 1 si algo se ha torcido.
#
#   ./dev-check.sh
V2=https://v2design.ivmotorclass.com
PR=https://ivmotorclass.com
R="--resolve v2design.ivmotorclass.com:443:127.0.0.1"
RP="--resolve ivmotorclass.com:443:127.0.0.1"
# The page these checks probe. While BRANDBOOK_ONLY is on, "/" is a holding
# page that loads one stylesheet, so probing it would report false failures.
PAGE=$(grep -q '^BRANDBOOK_ONLY=true' .env 2>/dev/null && echo /brandbook || echo /)
FAIL=0
ok(){ printf "  \033[32m✓\033[0m %s\n" "$1"; }
no(){ printf "  \033[31m✗\033[0m %s\n" "$1"; FAIL=1; }

hdr(){ curl -s -I -m 10 "$1" $2 2>/dev/null | grep -i "^cache-control" | tr -d '\r' | sed 's/cache-control: //I' | tr -d '\n'; }

echo "── Caché (v2design debe revalidar siempre) ──"
for f in /css/v2.css /js/saved-vehicles.js; do
  h=$(hdr "$V2$f" "$R")
  [[ "$h" == *"no-cache"* ]] && ok "$f → $h" || no "$f → '$h' (se esperaba no-cache)"
done
h=$(hdr "$V2$PAGE" "$R")
# no-store is stricter than no-cache and satisfies the same intent
[[ "$h" == *"no-cache"* || "$h" == *"no-store"* ]] && ok "HTML $PAGE → $h" || no "HTML $PAGE → '$h'"

echo "── Versionado automático de assets ──"
n=$(curl -s -m 10 "$V2$PAGE" $R | grep -cE '(css|js)/[a-z0-9./-]+\?v=[0-9]+')
[ "$n" -ge 2 ] && ok "$n assets con ?v=<fecha>" || no "sólo $n assets versionados (¿se perdió VersionedUrlGenerator?)"
d=$(curl -s -m 10 "$V2$PAGE" $R | grep -c 'v=[0-9]*[?&]v=')
[ "$d" -eq 0 ] && ok "sin versiones duplicadas" || no "$d URLs con ?v= duplicado"

echo "── PHP ──"
grep -q "revalidate_freq\] = 0" /etc/php/8.3/fpm/pool.d/motorclass-v2.conf 2>/dev/null \
  && ok "OPcache revalida en cada petición" || no "OPcache no revalida al instante"
grep -q "php8.3-fpm-v2.sock" /etc/nginx/sites-available/motorclass-v2 2>/dev/null \
  && ok "pool PHP-FPM propio" || no "v2 comparte el pool con producción"
for c in bootstrap/cache/config.php bootstrap/cache/routes-v7.php; do
  [ -f "$(dirname "$0")/$c" ] && no "$c existe (congela cambios)" || ok "$c ausente"
done

echo "── Aislamiento ──"
HERE="$(cd "$(dirname "$0")" && pwd)"
PROD_DIR=/var/www/motorclass

INO_P=$(stat -c %i "$PROD_DIR/database/database.sqlite" 2>/dev/null)
INO_V=$(stat -c %i "$HERE/database/database.sqlite" 2>/dev/null)
[ -n "$INO_V" ] && [ "$INO_P" != "$INO_V" ] \
  && ok "bases de datos distintas ($INO_P vs $INO_V)" || no "MISMA base de datos que produccion"

KEY_P=$(grep -m1 '^APP_KEY=' "$PROD_DIR/.env" 2>/dev/null | md5sum | cut -c1-10)
KEY_V=$(grep -m1 '^APP_KEY=' "$HERE/.env" 2>/dev/null | md5sum | cut -c1-10)
[ "$KEY_P" != "$KEY_V" ] \
  && ok "APP_KEY distinta" || no "MISMA APP_KEY que produccion"

readlink "$HERE/public/storage" | grep -q motorclass-v2 \
  && ok "public/storage apunta a v2" || no "public/storage apunta fuera de v2"

echo "── Bloqueo del entorno (BRANDBOOK_ONLY) ──"
if grep -q '^BRANDBOOK_ONLY=true' .env 2>/dev/null; then
  c=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "$V2/brandbook" $R)
  [ "$c" = "200" ] && ok "/brandbook accesible" || no "/brandbook devuelve $c"
  leaked=0
  for p in / /catalog /admin /login /register /sell-car; do
    t=$(curl -s -m 10 "$V2$p" $R | grep -c "sólo está el brandbook")
    [ "$t" -eq 0 ] && { no "$p NO está bloqueada"; leaked=1; }
  done
  [ "$leaked" -eq 0 ] && ok "resto de rutas bloqueadas (6 comprobadas)"
  # the brandbook renders real photographs through /img, so that route must survive
  src=$(curl -s -m 10 "$V2/brandbook" $R | grep -oE 'src="[^"]*/img/[^"]*"' | head -1 | sed 's/src="//;s/"//')
  if [ -n "$src" ]; then
    i=$(curl -s -o /dev/null -w "%{http_code}" -m 10 "$src" $R)
    [ "$i" = "200" ] && ok "servicio de imágenes sigue activo" || no "/img devuelve $i"
  else
    no "el brandbook no referencia ninguna imagen vía /img"
  fi
else
  ok "bloqueo desactivado (el sitio completo está servido)"
fi

echo "── Producción intacta ──"
h=$(hdr "$PR/css/app.css" "$RP")
[[ "$h" == *"immutable"* ]] && ok "sigue cacheando fuerte ($h)" || no "su caché ha cambiado → '$h'"
curl -s -m 10 "$PR/" $RP | grep -q "v2.css" && no "¡producción carga v2.css!" || ok "no carga assets de v2"
for p in / /catalog; do
  c=$(curl -s -o /dev/null -m 10 -w "%{http_code}" "$PR$p" $RP)
  [ "$c" = "200" ] && ok "producción $p → 200" || no "producción $p → $c"
done

echo
[ $FAIL -eq 0 ] && echo "  Todo correcto." || echo "  Hay problemas arriba."
exit $FAIL
