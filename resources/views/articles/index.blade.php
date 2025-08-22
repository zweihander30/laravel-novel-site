{{-- resources/views/articles/index.blade.php --}}
@foreach ($articles as $a)
  <article class="mb-4">
    <h2 class="font-bold">{{ $a->title }}</h2>
    <p class="text-slate-700">{{ $leads[$a->id] }}</p>
  </article>
@endforeach

