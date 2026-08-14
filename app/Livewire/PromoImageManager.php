<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PromoImageManager extends Component
{
    use WithFileUploads;

    // The folder relative to /public/images/
    const PROMO_DIR = 'images/prmotion_images';

    public array $images = [];         // [slot => filename]
    public ?int $replacingSlot = null; // which slot is being replaced
    public $newImage = null;           // uploaded temp file
    public bool $showAddModal = false;
    public $addImage = null;           // new image for "add more"

    public function mount(): void
    {
        $this->loadImages();
    }

    private function loadImages(): void
    {
        $dir = public_path(self::PROMO_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $files = collect(glob($dir . '/*.{png,jpg,jpeg,webp,gif}', GLOB_BRACE))
            ->map(fn($f) => basename($f))
            ->sortBy(fn($name) => (int) pathinfo($name, PATHINFO_FILENAME))
            ->values()
            ->toArray();

        $this->images = [];
        foreach ($files as $file) {
            $slot = pathinfo($file, PATHINFO_FILENAME); // "1", "2", "10", etc.
            $this->images[$slot] = $file;
        }
    }

    public function startReplace(string $slot): void
    {
        $this->replacingSlot = $slot;
        $this->newImage = null;
        $this->reset('newImage');
    }

    public function cancelReplace(): void
    {
        $this->replacingSlot = null;
        $this->reset('newImage');
    }

    public function confirmReplace(): void
    {
        $this->validate([
            'newImage' => 'required|image|max:12288',
        ]);

        $slot = $this->replacingSlot;
        $dir = public_path(self::PROMO_DIR);

        // Delete old file if exists
        if (isset($this->images[$slot])) {
            $oldPath = $dir . '/' . $this->images[$slot];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        // Save new file with same slot name, preserving original extension
        $ext = $this->newImage->getClientOriginalExtension() ?: 'png';
        $filename = $slot . '.' . $ext;
        $this->newImage->move($dir, $filename);

        $this->replacingSlot = null;
        $this->reset('newImage');
        $this->loadImages();

        $this->dispatch('notify', type: 'success', message: "Image {$slot} replaced successfully.");
    }

    public function deleteImage(string $slot): void
    {
        if (isset($this->images[$slot])) {
            $path = public_path(self::PROMO_DIR . '/' . $this->images[$slot]);
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Image {$slot} deleted.");
    }

    public function openAddModal(): void
    {
        $this->showAddModal = true;
        $this->addImage = null;
        $this->reset('addImage');
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->reset('addImage');
    }

    public function confirmAdd(): void
    {
        $this->validate([
            'addImage' => 'required|image|max:12288',
        ]);

        $dir = public_path(self::PROMO_DIR);

        // Find next available slot number
        $existingSlots = array_map('intval', array_keys($this->images));
        $next = empty($existingSlots) ? 1 : (max($existingSlots) + 1);

        $ext = $this->addImage->getClientOriginalExtension() ?: 'png';
        $filename = $next . '.' . $ext;
        $this->addImage->move($dir, $filename);

        $this->showAddModal = false;
        $this->reset('addImage');
        $this->loadImages();

        $this->dispatch('notify', type: 'success', message: "Image added as slot {$next}.");
    }

    public function render()
    {
        // Build display list: always show slots 1 and 5 as placeholders if missing
        $required = ['1', '5'];
        $displaySlots = collect(array_keys($this->images))
            ->merge($required)
            ->unique()
            ->sortBy(fn($v) => (int)$v)
            ->values()
            ->toArray();

        return view('livewire.promo-image-manager', [
            'displaySlots' => $displaySlots,
            'promoDir'     => self::PROMO_DIR,
        ]);
    }
}
