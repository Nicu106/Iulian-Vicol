<?php

namespace App\Http\Requests;

use App\Http\Controllers\SellCarController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Validator;

/**
 * Everything that has to be true before a submission becomes a row.
 *
 * Three layers, because no single one holds:
 *   VALIDATION  shape and size — a year is a year, a photograph is a photograph.
 *   TRAPS       a hidden field bots fill, and a signed clock. Neither asks the
 *               person anything, which is the point: a CAPTCHA charges every
 *               honest seller for the few who are not.
 *   CONTENT     the shape of spam. A private seller describing their own car
 *               does not paste links, HTML, or Cyrillic into a Spanish form.
 */
class SellCarRequest extends FormRequest
{
    /** Under four seconds nobody has read the page, chosen a marque, typed a
     *  model, a year, kilometres, a name and a phone number, and attached
     *  photographs. Measured against a person filling it as fast as they can:
     *  about 25 s. Four is generous and costs an honest visitor nothing. */
    private const MIN_SECONDS = 4;

    /** A signed form is only good for one sitting. Past this the token is stale
     *  and the page has to be reloaded — which also re-arms CSRF. */
    private const MAX_MINUTES = 120;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $marques = array_merge(array_column(SellCarController::MARQUES, 'key'), ['otra']);

        return [
            'brand'        => ['required', 'string', 'in:' . implode(',', $marques)],
            'brand_other'  => ['nullable', 'string', 'max:60', 'required_if:brand,otra'],
            'model'        => ['required', 'string', 'max:100'],
            'year'         => ['required', 'integer', 'min:1990', 'max:' . date('Y')],
            'mileage'      => ['required', 'integer', 'min:0', 'max:1500000'],
            'fuel'         => ['nullable', 'string', 'in:' . implode(',', SellCarController::FUEL)],
            'transmission' => ['nullable', 'string', 'in:' . implode(',', SellCarController::GEAR)],
            'price'        => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'seller_name'  => ['required', 'string', 'max:120'],
            'seller_phone' => ['required', 'string', 'max:30', 'regex:/^[+\d][\d\s().-]{6,}$/'],
            'seller_email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'photos'       => ['nullable', 'array', 'max:' . SellCarController::MAX_PHOTOS],
            'photos.*'     => ['file', 'mimetypes:image/jpeg,image/png,image/webp,image/heic,image/heif',
                               'max:' . SellCarController::MAX_PHOTO_KB, 'dimensions:min_width=200,min_height=200'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand.required'          => 'Dime la marca.',
            'brand_other.required_if' => 'Dime qué marca es.',
            'model.required'          => 'Dime el modelo.',
            'year.required'           => 'Dime el año.',
            'mileage.required'        => 'Dime los kilómetros, aunque sea aproximado.',
            'seller_name.required'    => 'Dime cómo te llamas.',
            'seller_phone.required'   => 'Necesito un teléfono para contestarte.',
            'seller_phone.regex'      => 'Ese teléfono no parece un teléfono.',
            'seller_email.email'      => 'Ese correo no existe.',
            'photos.max'              => 'Hasta ' . SellCarController::MAX_PHOTOS . ' fotos.',
            'photos.*.max'            => 'Una de las fotos pesa más de 12 MB.',
            'photos.*.mimetypes'      => 'Uno de los archivos no es una foto.',
            'photos.*.dimensions'     => 'Una de las fotos es demasiado pequeña para ver nada.',
        ];
    }

    public function withValidator(Validator $v): void
    {
        $v->after(function (Validator $v) {
            // ---- the trap -------------------------------------------------
            // A field no person can see or tab to. Anything in it filled the
            // form by reading the HTML, which is what a bot does.
            if (filled($this->input('apellido_2'))) {
                $v->errors()->add('brand', 'No he podido enviar el formulario. Recarga la página e inténtalo otra vez.');
                $this->flag('honeypot');
                return;
            }

            // ---- the clock ------------------------------------------------
            // Encrypted, so it cannot be back-dated; absent or unreadable means
            // the form was posted without ever loading the page.
            try {
                $issued = (int) Crypt::decryptString((string) $this->input('t'));
            } catch (\Throwable) {
                $issued = 0;
            }
            $age = time() - $issued;
            if ($issued === 0 || $age < self::MIN_SECONDS) {
                $v->errors()->add('brand', 'No he podido enviar el formulario. Recarga la página e inténtalo otra vez.');
                $this->flag('too-fast:' . $age);
                return;
            }
            if ($age > self::MAX_MINUTES * 60) {
                $v->errors()->add('brand', 'La página llevaba mucho tiempo abierta. Recárgala y vuelve a enviarla.');
                return;
            }

            // ---- the shape of spam ----------------------------------------
            $short = trim((string) $this->input('model') . ' ' . $this->input('seller_name') . ' ' . $this->input('brand_other'));
            $long  = (string) $this->input('description');

            // Links. A seller describing their own car does not paste one, and
            // essentially every automated submission does.
            if (self::hasLink($short) || self::hasLink($long)) {
                $v->errors()->add('description', 'Quita los enlaces del texto y vuelve a enviarlo.');
                $this->flag('link');
            }
            // Markup: <a>, [url], {{ }} — template or injection attempts.
            if (preg_match('/<[a-z\/!]|\[\/?(?:url|link|img)\b|\{\{/i', $short . ' ' . $long)) {
                $v->errors()->add('description', 'Quita el código del texto y vuelve a enviarlo.');
                $this->flag('markup');
            }
            // A Spanish form filled in Cyrillic or CJK is not a neighbour in
            // Málaga. Accented Spanish is Latin and is not touched by this.
            if (preg_match('/[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{0600}-\x{06FF}]/u', $short . ' ' . $long)) {
                $v->errors()->add('description', 'Escríbelo en español, por favor.');
                $this->flag('script');
            }
            // A model is a model: "Golf", "A4 Avant", "Serie 3". Not a sentence.
            if (str_word_count((string) $this->input('model')) > 8) {
                $v->errors()->add('model', 'Sólo el modelo — lo demás va en la descripción.');
            }
        });
    }

    /** Bare domains too: bots often drop the scheme to get past a naive filter. */
    private static function hasLink(string $s): bool
    {
        return (bool) preg_match('~(?:https?://|www\.)|\b[a-z0-9-]+\.(?:com|net|org|ru|cn|xyz|top|info|biz|club|online|site|shop)\b~i', $s);
    }

    /** Why it was refused, in the log, so a real seller who trips a rule can be
     *  found and helped rather than silently lost. */
    private function flag(string $why): void
    {
        \Log::info('sell-car refused: ' . $why, ['ip' => $this->ip(), 'ua' => substr((string) $this->userAgent(), 0, 120)]);
    }
}
