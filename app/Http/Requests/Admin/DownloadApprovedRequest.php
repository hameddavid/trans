<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class DownloadApprovedRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'transcript_type' => 'required|string',
            'index' => 'required',
        ];
    }
}
