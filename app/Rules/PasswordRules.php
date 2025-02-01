<?php

namespace App\Rules;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\UncompromisedVerifier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class PasswordRules extends Password
{
    /**
     * If the password requires at least one symbol.
     *
     * @var string
     */
    protected $preg = '/\p{Z}|\p{S}|\p{P}/u';

    /**
     * Makes the password require at least one symbol.
     *
     * @param string $preg
     * @return $this
     */
    public function symbols($preg = '/[@$!%*#?&]/')
    {
        $this->symbols = true;
        $this->preg = $preg;

        return $this;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->messages = [];

        $validator = Validator::make(
            $this->data,
            [$attribute => array_merge(['string', 'min:'.$this->min], $this->customRules)],
            $this->validator->customMessages,
            $this->validator->customAttributes
        )->after(function ($validator) use ($attribute, $value) {
            if (! is_string($value)) {
                return;
            }

            if ($this->mixedCase && ! preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
                $validator->errors()->add(
                    $attribute,
                    $this->getErrorMessage('validation.password.mixed')
                );
            }

            if ($this->letters && ! preg_match('/\pL/u', $value)) {
                $validator->errors()->add(
                    $attribute,
                    $this->getErrorMessage('validation.password.letters')
                );
            }

            if ($this->symbols && ! preg_match($this->preg, $value)) {
                $message = $this->getErrorMessage('validation.password.symbols');
                $message = ($this->preg == '') ? str_replace(' of :preg', '', $message) : str_replace(':preg', implode(', ', str_split(trim($this->preg, '/[]'))), $message);
                $validator->errors()->add(
                    $attribute,
                    $message
                );
            }

            if ($this->numbers && ! preg_match('/\pN/u', $value)) {
                $validator->errors()->add(
                    $attribute,
                    $this->getErrorMessage('validation.password.numbers')
                );
            }
        });

        if ($validator->fails()) {
            return $this->fail($validator->messages()->all());
        }

        if ($this->uncompromised && ! Container::getInstance()->make(UncompromisedVerifier::class)->verify([
            'value' => $value,
            'threshold' => $this->compromisedThreshold,
        ])) {
            return $this->fail($this->getErrorMessage('validation.password.uncompromised'));
        }

        return true;
    }
}
