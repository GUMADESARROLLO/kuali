<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'description' => 'required|string|min:10',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'priority' => 'required|in:baja,media,alta,urgente',
            'department_id' => 'sometimes|required|exists:departments,id',
            'attachments' => 'sometimes|array|max:5',
            'attachments.*' => 'file|mimes:pdf,png,jpg,jpeg,docx,xlsx,zip|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.min' => 'El título debe tener al menos 5 caracteres.',
            'description.required' => 'La descripción es obligatoria.',
            'description.min' => 'La descripción debe tener al menos 10 caracteres.',
            'category_id.required' => 'Debes seleccionar una categoría.',
            'subcategory_id.required' => 'Debes seleccionar una subcategoría.',
            'attachments.*.mimes' => 'Solo se permiten archivos: pdf, png, jpg, docx, xlsx, zip.',
            'attachments.*.max' => 'Cada archivo debe ser menor a 10MB.',
        ];
    }
}
