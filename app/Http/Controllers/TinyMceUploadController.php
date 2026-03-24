<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\MediaAsset;

class TinyMceUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240', // 10MB max
            'directory' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $directory = $request->input('directory', 'tinymce-uploads');
        
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $cleanName = Str::slug($baseName) . '-' . Str::random(8) . '.' . $ext;
        $storedPath = $directory . '/' . $cleanName;
        
        // Store the file
        $file->storeAs($directory, $cleanName, 'public');
        
        // Optionally create MediaAsset record
        try {
            $cleanTitle = Str::of($baseName)
                ->replace(['-', '_'], ' ')
                ->title()
                ->toString();

            MediaAsset::create([
                'title' => $cleanTitle,
                'file' => $storedPath,
                'extension' => $ext,
                'alt_text' => null,
                'content' => null,
            ]);
        } catch (\Exception $e) {
            // If MediaAsset creation fails, still return the image URL
            \Log::warning('Failed to create MediaAsset for TinyMCE upload: ' . $e->getMessage());
        }
        
        // Return the URL for TinyMCE
        return response()->json([
            'location' => Storage::disk('public')->url($storedPath)
        ]);
    }
}