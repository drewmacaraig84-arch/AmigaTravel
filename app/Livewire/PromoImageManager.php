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

    // [slot => ['url' => string, 'source' => 'storage'|'legacy']]
    public array  $images        = [];
    public ?string $replacingSlot = null;
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
                    $slot = pathinfo($basename, PATHINFO_FILENAME);
                    $this->images[$slot] = [
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
                    $slot = pathinfo($basename, PATHINFO_FILENAME);
                    // Don't overwrite if Storage already has this slot
                    if (!isset($this->images[$slot])) {
                        $this->images[$slot] = [
                            'url'    => asset(self::LEGACY_DIR . '/' . $basename),
                            'source' => 'legacy',
                            'file'   => $basename,
                        ];
                    }
                }
            }
        }

        // Sort by numeric slot
        uksort($this->images, fn($a, $b) => (int)$a <=> (int)$b);
    }

    public function startReplace(string $slot): void
    {
        $this->replacingSlot = $slot;
        $this->reset('newImage');
    }

    public function cancelReplace(): void
    {
        $this->replacingSlot = null;
        $this->reset('newImage');
    }

    public function confirmReplace(): void
    {
        $this->validate(['newImage' => 'required|image|max:20480']);

        $slot = $this->replacingSlot;

        // Delete old from storage if it was stored there
        if (isset($this->images[$slot]) && $this->images[$slot]['source'] === 'storage') {
            Storage::disk(self::DISK)->delete(self::STORE_DIR . '/' . $this->images[$slot]['file']);
        }
        // Try to delete legacy file (may fail on read-only filesystems like Railway)
        $legacyPath = public_path(self::LEGACY_DIR . '/' . ($this->images[$slot]['file'] ?? ''));
        if (file_exists($legacyPath)) {
            try { @unlink($legacyPath); } catch (\Throwable) {}
        }

        $ext = $this->newImage->getClientOriginalExtension() ?: 'png';
        $this->newImage->storeAs(self::STORE_DIR, $slot . '.' . $ext, self::DISK);

        $this->replacingSlot = null;
        $this->reset('newImage');
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Slot {$slot} replaced successfully.");
    }

    public function deleteImage(string $slot): void
    {
        if (isset($this->images[$slot])) {
            $info = $this->images[$slot];
            if ($info['source'] === 'storage') {
                Storage::disk(self::DISK)->delete(self::STORE_DIR . '/' . $info['file']);
            } else {
                // Legacy public path — may be read-only on Railway, skip silently
                $path = public_path(self::LEGACY_DIR . '/' . $info['file']);
                try { if (file_exists($path)) @unlink($path); } catch (\Throwable) {}
            }
        }
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Slot {$slot} deleted.");
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

        $slots = array_map('intval', array_keys($this->images));
        $next  = empty($slots) ? 1 : (max($slots) + 1);

        $ext = $this->addImage->getClientOriginalExtension() ?: 'png';
        $this->addImage->storeAs(self::STORE_DIR, $next . '.' . $ext, self::DISK);

        $this->showAddModal = false;
        $this->reset('addImage');
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Image added as slot {$next}.");
    }

    public function render()
    {
        $required     = ['1', '5'];
        $displaySlots = collect(array_keys($this->images))
            ->merge($required)
            ->unique()
            ->sortBy(fn($v) => (int)$v)
            ->values()
            ->toArray();

        return view('livewire.promo-image-manager', [
            'displaySlots' => $displaySlots,
        ]);
    }
}
