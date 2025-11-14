<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SymlinkController extends Controller
{
    public function createStorageLink()
    {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (File::exists($link)) {
            if (File::isWritable($link)) {
                File::delete($link);
            } else {
                return "Error: Existing symlink 'public/storage' is not writable. Please delete it manually or check permissions.";
            }
        }

        try {
            \symlink($target, $link);
            return "Storage symlink created successfully!";
        } catch (\Exception $e) {
            return "Error creating storage symlink: " . $e->getMessage();
        }
    }
}
