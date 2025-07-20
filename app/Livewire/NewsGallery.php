<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\News;

class NewsGallery extends Component
{
    public News $news;
    public $selectedImageIndex = 0;
    public $showModal = false;
    public $galleryImages = [];

    public function mount(News $news)
    {
        $this->news = $news;
        $this->galleryImages = $news->gallery ?? [];
    }

    public function openModal($index)
    {
        $this->selectedImageIndex = $index;
        $this->showModal = true;
        $this->dispatch('modal-opened');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->dispatch('modal-closed');
    }

    public function nextImage()
    {
        if (count($this->galleryImages) <= 1) return;
        
        $this->selectedImageIndex = ($this->selectedImageIndex + 1) % count($this->galleryImages);
        $this->dispatch('image-changed', index: $this->selectedImageIndex);
    }

    public function previousImage()
    {
        if (count($this->galleryImages) <= 1) return;
        
        $this->selectedImageIndex = ($this->selectedImageIndex - 1 + count($this->galleryImages)) % count($this->galleryImages);
        $this->dispatch('image-changed', index: $this->selectedImageIndex);
    }

    public function goToImage($index)
    {
        if ($index >= 0 && $index < count($this->galleryImages)) {
            $this->selectedImageIndex = $index;
            $this->dispatch('image-changed', index: $this->selectedImageIndex);
        }
    }

    public function getCurrentImage()
    {
        return $this->galleryImages[$this->selectedImageIndex] ?? null;
    }

    public function getImageUrl($image)
    {
        return asset('storage/' . $image);
    }

    public function render()
    {
        return view('livewire.news-gallery');
    }
}