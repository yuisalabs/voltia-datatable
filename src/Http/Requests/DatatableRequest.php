<?php

namespace Yuisa\VoltiaDatatable\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DatatableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:' . config('voltia-datatable.max_per_page')],
            'search' => ['nullable', 'string'],
            'sortBy' => ['nullable', 'string'],
            'sortDirection' => ['nullable', 'in:asc,desc'],
            'filters' => ['nullable', 'array'],
        ];
    }
}