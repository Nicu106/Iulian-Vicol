<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Validator;

/**
 * /recomienda asks for two things, and gets the same traps as /vende: a field
 * bots fill, a signed clock, and the shape of spam. No CAPTCHA.
 */
class ReferralRequest extends FormRequest
{
    /** Two fields: a person needs a few seconds, a script needs none. */
    private const MIN_SECONDS = 3;
    private const MAX_MINUTES = 120;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[+\d][\d\s().-]{6,}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Dime cómo te llamas.',
            'phone.required' => 'Necesito tu teléfono para saber que el enlace es tuyo.',
            'phone.regex'    => 'Ese teléfono no parece un teléfono.',
        ];
    }

    public function withValidator(Validator $v): void
    {
        $v->after(function (Validator $v) {
            $again = 'No he podido crear el enlace. Recarga la página e inténtalo otra vez.';

            if (filled($this->input('apellido_2'))) {
                $v->errors()->add('name', $again);
                return;
            }

            try {
                $issued = (int) Crypt::decryptString((string) $this->input('t'));
            } catch (\Throwable) {
                $issued = 0;
            }
            $age = time() - $issued;
            if ($issued === 0 || $age < self::MIN_SECONDS) {
                $v->errors()->add('name', $again);
                return;
            }
            if ($age > self::MAX_MINUTES * 60) {
                $v->errors()->add('name', 'La página llevaba mucho tiempo abierta. Recárgala y vuelve a intentarlo.');
                return;
            }

            $name = (string) $this->input('name');
            if (preg_match('~(?:https?://|www\.)|<[a-z/!]|[\x{0400}-\x{04FF}\x{4E00}-\x{9FFF}\x{0600}-\x{06FF}]~iu', $name)) {
                $v->errors()->add('name', 'Escribe sólo tu nombre.');
            }
        });
    }
}
