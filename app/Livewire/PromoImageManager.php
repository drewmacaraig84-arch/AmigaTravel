<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class PromoImageManager extends Component
{
    use WithFileUploads;

    const DISK        = 'public';
    const PROMO_DIR   = 'prmotion_images'; // lives in storage/app/public/prmotion_images (persistent on Railway)

    public array  $images        = [];  // [slot => filename]
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
        // Ensure directory exists
        if (! Storage::disk(self::DISK)->exists(self::PROMO_DIR)) {
            Storage::disk(self::DISK)->makeDirectory(self::PROMO_DIR);
        }

        $extensions = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'jfif'];
        $files = collect(Storage::disk(self::DISK)->files(self::PROMO_DIR))
            ->map(fn($f) => basename($f))
            ->filter(fn($f) => in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $extensions))
            ->sortBy(fn($f) => (int) pathinfo($f, PATHINFO_FILENAME))
            ->values()
            ->toArray();

        $this->images = [];
        foreach ($files as $file) {
            $slot = pathinfo($file, PATHINFO_FILENAME);
            $this->images[$slot] = $file;
        }
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
        $this->validate(['newImage' => 'required|image|max:12288']);

        $slot = $this->replacingSlot;

        // Delete old file
        if (isset($this->images[$slot])) {
            Storage::disk(self::DISK)->delete(self::PROMO_DIR . '/' . $this->images[$slot]);
        }

        // Save with slot-number filename so website carousel can read it
        $ext      = $this->newImage->getClientOriginalExtension() ?: 'png';
        $filename = self::PROMO_DIR . '/' . $slot . '.' . $ext;
        $this->newImage->storeAs(self::PROMO_DIR, $slot . '.' . $ext, self::DISK);

        $this->replacingSlot = null;
        $this->reset('newImage');
        $this->loadImages();

        $this->dispatch('notify', type: 'success', message: "Image {$slot} replaced successfully.");
    }

    public function deleteImage(string $slot): void
    {
        if (isset($this->images[$slot])) {
            Storage::disk(self::DISK)->delete(self::PROMO_DIR . '/' . $this->images[$slot]);
        }
        $this->loadImages();
        $this->dispatch('notify', type: 'success', message: "Image {$slot} deleted.");
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
        $this->validate(['addImage' => 'required|image|max:12288']);

        // Next sequential slot
        $slots = array_map('intval', array_keys($this->images));
        $next  = empty($slots) ? 1 : (max($slots) + 1);

        $ext = $this->addImage->getClientOriginalExtension() ?: 'png';
        $this->addImage->storeAs(self::PROMO_DIR, $next . '.' . $ext, self::DISK);

        $this->showAddModal = false;
        $this->reset('addImage');
        $this->loadImages();

        $this->dispatch('notify', type: 'success', message: "Image added as slot {$next}.");
    }

    public function getImageUrl(string $slot): string
    {
        if (isset($this->images[$slot])) {
            return Storage::disk(self::DISK)->url(self::PROMO_DIR . '/' . $this->images[$slot]);
        }
        return '';
    }

    public function render()
    {
        $required     = ['1', '5'];
        $displaySlots = collect(array_keys($this->images))
            ->merge($required)
            ->unique()
            ->sortBy(fn($v) => (int) $v)
            ->values()
            ->toArray();

        $imageUrls = [];
        foreach ($this->images as $slot => $file) {
            $imageUrls[$slot] = Storage::disk(self::DISK)->url(self::PROMO_DIR . '/' . $file);
        }

        return view('livewire.promo-image-manager', [
            'displaySlots' => $displaySlots,
            'imageUrls'    => $imageUrls,
        ]);
    }
}
