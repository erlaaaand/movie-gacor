<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Tambahkan ini

class StoreMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ubah ke true agar request dapat diproses
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string', 'max:255', Rule::unique('movies', 'id')],
            'judul' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'sinopsis' => 'required|string',
            'tahun' => 'required|integer',
            'pemain' => 'required|string',
            'foto_sampul' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
