<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SharingItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class SharingController extends Controller
{
    public function index(Request $request)
    {
        $parentId = $request->get('folder');
        $currentFolder = null;
        $breadcrumbs = [];

        if ($parentId) {
            $currentFolder = SharingItem::findOrFail($parentId);
            
            // Check if this folder (or any ancestor) has a password and is not yet unlocked
            $folderToCheck = $currentFolder;
            while ($folderToCheck) {
                if ($folderToCheck->password) {
                    $unlockedFolders = session('sharing_unlocked_folders', []);
                    if (!in_array($folderToCheck->id, $unlockedFolders)) {
                        // Need to unlock this folder first
                        return redirect()->route('sharing.index', ['folder' => $folderToCheck->parent_id])
                            ->with('unlock_folder_id', $folderToCheck->id)
                            ->with('unlock_folder_name', $folderToCheck->name);
                    }
                }
                $folderToCheck = $folderToCheck->parent;
            }

            // Build breadcrumbs
            $temp = $currentFolder;
            while ($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }
        }

        $items = SharingItem::where('parent_id', $parentId)
            ->with('uploader')
            ->orderBy('type', 'desc')
            ->orderBy('name')
            ->get();

        $unlockFolderId = session('unlock_folder_id');
        $unlockFolderName = session('unlock_folder_name');

        return view('sharing.index', compact('items', 'currentFolder', 'breadcrumbs', 'unlockFolderId', 'unlockFolderName'));
    }

    public function unlockFolder(Request $request, SharingItem $sharing)
    {
        if (!$sharing->isFolder() || !$sharing->password) {
            return back()->with('error', 'Folder ini tidak memerlukan password.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $sharing->password)) {
            return back()->with('error', 'Password salah. Silakan coba lagi.')
                ->with('unlock_folder_id', $sharing->id)
                ->with('unlock_folder_name', $sharing->name);
        }

        // Store unlocked folder in session
        $unlockedFolders = session('sharing_unlocked_folders', []);
        if (!in_array($sharing->id, $unlockedFolders)) {
            $unlockedFolders[] = $sharing->id;
            session(['sharing_unlocked_folders' => $unlockedFolders]);
        }

        return redirect()->route('sharing.index', ['folder' => $sharing->id])
            ->with('success', 'Folder berhasil dibuka.');
    }

    public function createFolder(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:sharing_items,id',
            'password' => 'required|string|min:4|max:100',
        ]);

        SharingItem::create([
            'name' => $validated['name'],
            'type' => 'folder',
            'parent_id' => $validated['parent_id'],
            'user_id' => auth()->id(),
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Folder berhasil dibuat.');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
            'parent_id' => 'nullable|exists:sharing_items,id',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $name = $file->getClientOriginalName();
            $path = $file->store('sharing', 'public');

            SharingItem::create([
                'name' => $name,
                'type' => 'file',
                'mime_type' => $file->getMimeType(),
                'path' => $path,
                'size' => $file->getSize(),
                'parent_id' => $request->parent_id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'File berhasil diupload.');
        }

        return back()->with('error', 'Gagal mengupload file.');
    }

    public function download(Request $request, SharingItem $sharing)
    {
        if ($sharing->type !== 'file' || !Storage::disk('public')->exists($sharing->path)) {
            abort(404);
        }

        // Check if this file's parent folder has a password and is unlocked
        $folder = $sharing->parent;
        if ($folder && $folder->password) {
            $unlockedFolders = session('sharing_unlocked_folders', []);
            if (!in_array($folder->id, $unlockedFolders)) {
                return back()->with('error', 'Anda harus membuka folder terlebih dahulu dengan memasukkan password.');
            }
        }

        return Storage::disk('public')->download($sharing->path, $sharing->name);
    }

    public function destroy(SharingItem $sharing)
    {
        if ($sharing->type === 'file') {
            Storage::disk('public')->delete($sharing->path);
        } else {
            // Recursive delete for folder contents
            $this->deleteFolderContents($sharing);
        }

        $sharing->delete();

        return back()->with('success', 'Item berhasil dihapus.');
    }

    private function deleteFolderContents(SharingItem $folder)
    {
        foreach ($folder->children as $child) {
            if ($child->type === 'file') {
                Storage::disk('public')->delete($child->path);
            } else {
                $this->deleteFolderContents($child);
            }
            $child->delete();
        }
    }
}
