<?php

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JoinRequest extends FormRequest
{
    // The maxlength of the field, too
    public const int NICKNAME_MAX_LENGTH = 20;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['required', Rule::in(Player::AVATARS)],
            'nickname' => [
                'required',
                'string',
                'min:2',
                'max:'.self::NICKNAME_MAX_LENGTH,
                'regex:/^[\pL\pN _-]+$/u',
                // The bots keep their nicknames
                Rule::unique('players')->where('is_bot', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nickname.regex' => 'Letters, digits, spaces, "-" and "_" only.',
            'nickname.unique' => 'One of our bots has this nickname: they will not give it up!',
        ];
    }
}
