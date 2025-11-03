<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PhotoUploadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $event = $this->route('event');

        return [
            'file' => [
                'required',
                'file',
                'mimes:jpeg',
                'max:10240',
                Rule::dimensions()->maxWidth(2560)->maxHeight(1920),
            ],
            'taken_at' => [
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::date()
                    ->afterOrEqual($event->getUploadWindowStart())
                    ->beforeOrEqual($event->getUploadWindowEnd()),
            ],
        ];
    }
}
