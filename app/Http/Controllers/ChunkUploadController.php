<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkUploadController extends Controller
{
    /**
     * Handles chunked base64 upload.
     * Expects: base64_chunk, chunk_index, total_chunks, identifier, filename
     */
    public function uploadChunk(Request $request)
    {
        $validated = $request->validate([
            'base64_chunk' => 'required|string',
            'chunk_index' => 'required|integer',
            'total_chunks' => 'required|integer',
            'identifier' => 'required|string',
            'filename' => 'required|string',
        ]);

        $identifier = $validated['identifier'];
        $chunkIndex = $validated['chunk_index'];
        $totalChunks = $validated['total_chunks'];
        $filename = $validated['filename'];
        $chunkData = base64_decode($validated['base64_chunk']);

        // Store chunk temporarily
        $tempPath = "chunks/{$identifier}/chunk_{$chunkIndex}";
        Storage::disk('local')->put($tempPath, $chunkData);

        // Check if all chunks are uploaded
        $files = Storage::disk('local')->files("chunks/{$identifier}");
        
        if (count($files) === $totalChunks) {
            // Merge chunks
            $finalContent = '';
            for ($i = 0; $i < $totalChunks; $i++) {
                $finalContent .= Storage::disk('local')->get("chunks/{$identifier}/chunk_{$i}");
            }

            // Save final file
            $finalFilename = $identifier . '_' . $filename;
            $finalPath = "notifications/" . $finalFilename;
            Storage::disk('public')->put($finalPath, $finalContent);

            // Clean up chunks
            Storage::disk('local')->deleteDirectory("chunks/{$identifier}");

            return response()->json([
                'status' => 'completed',
                'path' => $finalPath,
                'filename' => $filename
            ]);
        }

        return response()->json([
            'status' => 'processing',
            'uploaded' => count($files),
            'total' => $totalChunks
        ]);
    }
}
