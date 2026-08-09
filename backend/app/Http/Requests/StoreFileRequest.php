<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'folder_id' => 'required|exists:folders,id',
            // max is in kilobytes: 20480 KB = 20 MB
            'file' => 'required|file|max:20480',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please choose a file to upload.',
            'file.file' => 'The file failed to upload. It may exceed the server upload limit (check PHP upload_max_filesize / post_max_size).',
            'file.max' => 'The file may not be greater than 20 MB.',
        ];
    }
}
