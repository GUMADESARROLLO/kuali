<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => 'required|string|min:1|max:5000',
            'is_internal' => 'sometimes|boolean',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|mimes:pdf,png,jpg,jpeg,docx,xlsx,zip|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'El comentario no puede estar vacío.',
            'attachments.*.mimes' => 'Solo se permiten archivos: pdf, png, jpg, docx, xlsx, zip.',
            'attachments.*.max' => 'Cada archivo debe ser menor a 10MB.',
        ];
    }
}
