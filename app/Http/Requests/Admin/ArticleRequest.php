<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        // On laisse le middleware 'role:admin' gérer l'accès, 
        // ou on peut vérifier ici si besoin.
        return true;
    }

    /**
     * Règles de validation.
     */
    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('article');

        return [
            'title'            => 'required|string|max:255',
            'slug'             => 'nullable|string|unique:articles,slug,' . $id,
            'excerpt'          => 'required|string|max:500',
            'content'          => 'required|array', // JSON TipTap
            'category_id'      => 'nullable|exists:categories,id',
            'series_id'        => 'nullable|exists:series,id',
            'series_order'     => 'nullable|integer|min:1',
            'tag_ids'          => 'nullable|array',
            'tag_ids.*'        => 'exists:tags,id',
            'cover_image'      => 'nullable|string',
            'cover_gallery'    => 'nullable|array',
            'cover_type'       => 'nullable|in:image,gallery',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'canonical_url'    => 'nullable|url',
        ];
    }
}
