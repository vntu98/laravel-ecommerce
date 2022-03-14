<x-app-layout>
    <x-slot name="header">
        <div class="space-x-1">
            <a href="" class="text-indigo-500">
                @foreach ($category->ancestors->reverse() as $ancestor) 
                    <a href="/categories/{{ $ancestor->slug }}" class="text-indigo-500">
                        {{ $ancestor->title }}
                    </a>
                   @if(!$loop->last) <span class="font-bold text-gray-300">/</span> @endif
                @endforeach
            </a>
        </div>

        <h2 class="mt-1 font-semibold text-xl text-gray-800 leading-tight">
            {{ $category->title }}
        </h2>
    </x-slot>

    Product browser
</x-app-layout>