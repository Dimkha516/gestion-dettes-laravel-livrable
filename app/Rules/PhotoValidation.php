<?php
// app/Rules/PhotoValidation.php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhotoValidation implements Rule
{
    public function passes($attribute, $value)
    {
        return in_array($value->extension(), ['png', 'jpeg', 'jpg', 'svg'])
            && $value->getSize() <= 40960; // 40 KB
    }

    public function message()
    {
        return 'La photo doit être au format PNG, JPEG, JPG ou SVG et ne pas dépasser 40 Ko.';
    }
}
