@extends('layouts.app')
@section('content')
    <main class="max-w-[1200px] w-full mx-auto p-6 flex flex-col gap-5">

      <section class="flex items-end justify-between gap-4 flex-wrap" aria-label="Judul halaman">
        <div>
          <nav class="flex items-center gap-1.5 flex-wrap text-xs text-neutral-600 mb-1" aria-label="Breadcrumb">
            <a class="no-underline text-ink font-medium hover:text-accent" href="{{ route('dashboard') }}">Dashboard</a>
            <span class="text-neutral-400">/</span>
            <a class="no-underline text-ink font-medium hover:text-accent" href="{{ route('facilities.index') }}">Facilities</a>
            <span class="text-neutral-400">/</span>
            <span class="text-neutral-600">Edit Facility</span>
          </nav>
          <h2 class="text-[26px]">Edit Facility</h2>
          <p class="m-0 mt-1 text-[13px] text-neutral-600">Ubah data fasilitas yang sudah ada.</p>
        </div>
        <a href="{{ route('facilities.index') }}" class="btn btn-secondary">Kembali</a>
      </section>

      @if($errors->any())
        <div class="flex items-center gap-2 border-2 border-accent-800 bg-surface px-3 py-2 text-sm text-accent-800" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
          <span>Terdapat kesalahan pada isian form. Silakan periksa kembali.</span>
        </div>
      @endif

      <!-- Form edit facility -->
      <section class="card" aria-label="Form edit facility">
        <h3 class="card-title">Data Facility</h3>

        <form method="POST" action="{{ route('facilities.update', $facility) }}" class="flex flex-col gap-4">
          @csrf
          @method('PUT')

          <div class="field">
            <label class="field-label" for="name">Facility Name</label>
            <input class="input @error('name') border-accent-800 @enderror" type="text" id="name" name="name" value="{{ old('name', $facility->name) }}" required>
            @error('name')
              <span class="small text-accent-800">{{ $message }}</span>
            @enderror
          </div>

          <div class="field">
            <label class="field-label" for="description">Description</label>
            <textarea class="input @error('description') border-accent-800 @enderror" id="description" name="description" rows="5">{{ old('description', $facility->description) }}</textarea>
            @error('description')
              <span class="small text-accent-800">{{ $message }}</span>
            @enderror
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <a href="{{ route('facilities.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Facility</button>
          </div>

        </form>
      </section>

    </main>
@endsection
