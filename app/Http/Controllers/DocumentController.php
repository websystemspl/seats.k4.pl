<?php

namespace App\Http\Controllers;

use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request, $userId)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Brak uprawnień'], 403);
        }

        $documents = UserDocument::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(compact('documents'));
    }

    public function upload(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Brak uprawnień'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:diploma,other',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $request->user_id . '_' . $file->hashName();
        $file->storeAs('documents', $filename, 'local');

        $document = UserDocument::create([
            'user_id' => $request->user_id,
            'uploaded_by' => Auth::id(),
            'type' => $request->type,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return response()->json(compact('document'));
    }

    public function download(Request $request, $id)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $document = UserDocument::findOrFail($id);
        $path = storage_path('app/documents/' . $document->filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $document->original_name);
    }

    public function destroy(Request $request)
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Brak uprawnień'], 403);
        }

        $document = UserDocument::findOrFail($request->documentId);
        Storage::disk('local')->delete('documents/' . $document->filename);
        $document->delete();

        return response()->json(['success' => true]);
    }
}
