<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PromoImageManager extends Component
{
    use WithFileUploads;

    const DISK      = 'public';
    const STORE_DIR = 'prmotion_images';              // storage/app/public/prmotion_images
    const LEGACY_DIR = 'images/prmotion_images';      // public/images/prmotion_images (fallback)

    // [filename => ['url' => string, 'source' => 'storage'|'legacy', 'file' => string]]
    public array  $images        = [];
    public ?string $replacingFile = null;
    public        $newImage       = null;
    public bool   $showAddModal  = false;
    public        $addImage       = null;

    public function mount(): void
    {
        $this->loadImages();
    }

    private function loadImages(): void
    {
        $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'jfif'];
        $this->images = [];

        // --- Source 1: Storage disk (persistent Railway volume) ---
        if (Storage::disk(self::DISK)->exists(self::STORE_DIR)) {
            foreach (Storage::disk(self::DISK)->files(self::STORE_DIR) as $path) {
                $basename = basename($path);
                if (in_array(strtolower(pathinfo($basename, PATHINFO_EXTENSION)), $extensions, true)) {
                    $this->images[$basename] = [
                        'url'    => storage_asset_path(self::STORE_DIR . '/' . $basename),
                        'source' => 'storage',
                        'file'   => $basename,
                    ];
                }
            }
        }

        // --- Source 2: legacy public/images/prmotion_images ---
        $legacyDir = public_path(self::LEGACY_DIR);
        if (is_dir($legacyDir)) {
            foreach (scandir($legacyDir) as $basename) {
                if (in_array(strtolower(pathinfo($basename, PATHINFO_EXTENSION)), $extensions, true)) {
                    // Don't overwrite if Storage already has this file
                    if (!isset($this->images[$basename])) {
                        $this->images[$basename] = [
                            'url'    => asset(self::LEGACY_DIR . '/' . $basename),
                            'source' => 'legacy',
                            'file'   => $basename,
                        ];
                    }
                }
            }
        }

        // Sort alphabetically by filename
        ksort($this->images);
    }

    public function startReplace(string $filename): void
    {
        $this->replacingFile = $filename;
        $this->reset('newImage');
    }

    public function cancelReplace(): void
    {
        $this->replacingFile = null;
        $this->reset('newImage');
    }

    public function confirmReplace(): void
    {
        $this->validate(['newImage' => 'required|image|max:20480']);

        $filename = $this->replacingFile;

        // Delete old from storage if it was stored there
        if (isset($this->images[$filename]) && $this->images[$filename]['source'] === 'storage') {
            Storage::disk(self::DISK)->delete(self::STORE_DIR . '/' . $this->images[$filename]['file']);
        }
        // Try to delete legacy file (may fail on read-only filesystems like Railway)
        $legacyPath = public_path(self::LEGACY_DIR . '/' . ($this->images[$filename]['file'] ?? ''));
        if (file_exists($legacyPath)) {
            try { @unlink($legacyPath); } catch (\Throwable) {}
        }

        $newName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $this->newImage->getClientOriginalName());
        
        // Ensure unique filename if it already exists (and not replacing itself)
        if ($newName !== $filename && (Storage::disk(self::DISK)->exists(self::STORE_DIR . '/' . $newName) || file_exists(public_path(self::LEGACY_DIR . '/' . $newName)))) {
            $nameWithoutExt = pathinfo($newName, PATHINFO_FILENAME);
            $ext = pathinfo($newName, PATHINFO_EXTENSION);
            $newName = $nameWithoutExt . '_' . time() . '.' . $ext;
        }

        $this->newImage->storeAs(self::STORE_DIR, $newName, self::DISK);

        $this->replacingFile = null;
        $this->reset('newImage');
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Image replaced successfully.");
    }

    public function deleteImage(string $filename): void
    {
        if (isset($this->images[$filename])) {
            $info = $this->images[$filename];
            if ($info['source'] === 'storage') {
                Storage::disk(self::DISK)->delete(self::STORE_DIR . '/' . $info['file']);
            } else {
                // Legacy public path — may be read-only on Railway, skip silently
                $path = public_path(self::LEGACY_DIR . '/' . $info['file']);
                try { if (file_exists($path)) @unlink($path); } catch (\Throwable) {}
            }
        }
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Image deleted.");
    }

    public function openAddModal(): void
    {
        $this->showAddModal = true;
        $this->reset('addImage');
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->reset('addImage');
    }

    public function confirmAdd(): void
    {
        $this->validate(['addImage' => 'required|image|max:20480']);

        $newName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $this->addImage->getClientOriginalName());
        
        // Ensure unique filename if it already exists
        if (Storage::disk(self::DISK)->exists(self::STORE_DIR . '/' . $newName) || file_exists(public_path(self::LEGACY_DIR . '/' . $newName))) {
            $nameWithoutExt = pathinfo($newName, PATHINFO_FILENAME);
            $ext = pathinfo($newName, PATHINFO_EXTENSION);
            $newName = $nameWithoutExt . '_' . time() . '.' . $ext;
        }

        $this->addImage->storeAs(self::STORE_DIR, $newName, self::DISK);

        $this->showAddModal = false;
        $this->reset('addImage');
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Image added successfully.");
    }

    public function render()
    {
        return view('livewire.promo-image-manager', [
            'displayFiles' => array_keys($this->images),
        ]);
    }
}
