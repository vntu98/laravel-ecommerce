<div class="space-y-4">
    <img style="width: 500px; height: 400px" src="{{ $selectedImageUrl }}">

    <div class="grid grid-cols-6 gap-2">
        @foreach ($product->getMedia() as $media)
            <button wire:click="selectImage('{{ $media->getUrl() }}')">
                <img src="{{ $media->getUrl() }}">
            </button>
        @endforeach
    </div>
</div>
