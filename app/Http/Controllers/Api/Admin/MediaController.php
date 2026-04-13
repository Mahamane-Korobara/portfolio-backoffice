<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaController extends Controller
{
    /**
     * Liste des médias avec pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $media = Media::latest()
            ->when($request->search, fn($q) => $q->where('original_name', 'like', '%' . $request->search . '%'))
            ->paginate(24);

        return response()->json($media);
    }

    /**
     * Upload et enregistrement d'un média.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
            'alt'  => 'nullable|string|max:255',
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file     = $request->file('file');
        $filename = Str::uuid() . '.' . $file->extension();
        $path     = $file->storeAs('blog/media', $filename, 'public');

        $width = $height = null;

        try {
            $manager = new ImageManager(new Driver());
            $image   = $manager->decodePath($file->getRealPath());
            $width  = $image->width();
            $height = $image->height();
        } catch (\Exception $e) {
            // fichier non-image, dimensions ignorées
        }

        $media = Media::create([
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'url'           => Storage::url($path),
            'disk'          => 'public',
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'width'         => $width,
            'height'        => $height,
            'alt'           => $request->alt,
        ]);

        return response()->json($media, 201);
    }

    /**
     * Suppression définitive.
     */
    public function destroy(int $id): JsonResponse
    {
        $media = Media::findOrFail($id);

        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        return response()->json(['message' => 'Média supprimé définitivement.']);
    }
}
