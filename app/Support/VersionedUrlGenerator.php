<?php

namespace App\Support;

use Illuminate\Routing\UrlGenerator;

/**
 * Añade ?v=<fecha de modificación> a toda URL de asset local.
 *
 * Así nadie tiene que acordarse de nada: cualquier asset('css/x.css') o
 * asset('js/y.js') queda versionado solo. Si el fichero cambia, cambia la URL,
 * y ningún navegador ni proxy puede servir una copia vieja.
 *
 * Las URLs externas (http…) y los ficheros que no existen se devuelven intactos.
 */
class VersionedUrlGenerator extends UrlGenerator
{
    /** Caché en memoria por petición, para no hacer un stat por cada llamada. */
    protected array $stampCache = [];

    public function asset($path, $secure = null)
    {
        $url = parent::asset($path, $secure);

        // URLs absolutas (CDN, Google Fonts…) no se tocan
        if ($this->isValidUrl($path)) {
            return $url;
        }

        $stamp = $this->assetStamp($path);

        if ($stamp === null) {
            return $url;
        }

        return $url.(str_contains($url, '?') ? '&' : '?').'v='.$stamp;
    }

    protected function assetStamp(string $path): ?int
    {
        if (array_key_exists($path, $this->stampCache)) {
            return $this->stampCache[$path];
        }

        $relative = ltrim(parse_url($path, PHP_URL_PATH) ?: $path, '/');
        $file = public_path($relative);

        $stamp = is_file($file) ? @filemtime($file) : null;

        return $this->stampCache[$path] = ($stamp ?: null);
    }
}
