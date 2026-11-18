@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto space-y-5">
  <a href="{{ route('client-control.admin.decisions.show', $decision->id) }}" class="text-sm text-teal-700">← Decision details</a>
  <div><h1 class="text-3xl font-bold text-[#0F2A52]">Edit draft · {{ $decision->reference }}</h1>
    <p class="text-slate-500">Changes are permitted only while this decision remains a draft. Project and reference cannot be changed.</p></div>
  @if($errors->any())
    <div class="rounded-xl bg-red-50 p-4 text-red-800"><strong>Please correct:</strong>
      <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif
  <form action="{{ route('client-control.admin.decisions.update', $decision->id) }}" method="POST" enctype="multipart/form-data" class="bg-white border rounded-2xl p-5 sm:p-8 space-y-5">
    @csrf
    @method('PUT')
    <div class="grid sm:grid-cols-2 gap-4">
      <label class="block text-sm font-semibold">Category *
        <select name="category" required class="block mt-2 w-full rounded-xl border-slate-300">
          @foreach(['Design','Materials','Finishes','Layout','Commercial','Other'] as $c)
            <option value="{{ $c }}" @selected(old('category', $decision->category) === $c)>{{ $c }}</option>
          @endforeach
        </select>
      </label>
      <label class="block text-sm font-semibold">Response due date
        <input type="date" name="response_due_at" value="{{ old('response_due_at', $decision->response_due_at) }}" class="block mt-2 w-full rounded-xl border-slate-300">
      </label>
    </div>
    <label class="block text-sm font-semibold">Decision subject *
      <input name="title" required maxlength="255" value="{{ old('title', $decision->title) }}" class="block mt-2 w-full rounded-xl border-slate-300">
    </label>
    <label class="block text-sm font-semibold">Description / client instructions *
      <textarea name="description" required rows="5" class="block mt-2 w-full rounded-xl border-slate-300">{{ old('description', $decision->description) }}</textarea>
    </label>
    <div class="rounded-xl bg-slate-50 p-4 space-y-3">
      <h2 class="font-bold text-[#0F2A52]">Available choices</h2>
      <p class="text-sm text-slate-500">Up to three choices. Blank labels are omitted.</p>
      @for($i = 0; $i < 3; $i++)
        <div class="grid sm:grid-cols-2 gap-2">
          <input name="options[{{ $i }}][label]" maxlength="200" value="{{ old('options.'.$i.'.label', $options->get($i)?->label) }}" placeholder="Option {{ $i + 1 }} label" class="rounded-xl border-slate-300">
          <input name="options[{{ $i }}][details]" maxlength="2000" value="{{ old('options.'.$i.'.details', $options->get($i)?->details) }}" placeholder="Optional explanation" class="rounded-xl border-slate-300">
        </div>
      @endfor
    </div>
    <div class="flex flex-wrap gap-5">
      <label class="flex gap-2 items-center"><input type="checkbox" name="has_cost_impact" value="1" @checked(old('has_cost_impact', $decision->has_cost_impact))> Cost impact</label>
      <label class="flex gap-2 items-center"><input type="checkbox" name="has_time_impact" value="1" @checked(old('has_time_impact', $decision->has_time_impact))> Time impact</label>
    </div>
    <label class="block text-sm font-semibold">Cost / schedule implications
      <textarea name="impact_details" rows="3" class="block mt-2 w-full rounded-xl border-slate-300">{{ old('impact_details', $decision->impact_details) }}</textarea>
    </label>
    <div class="space-y-2">
      <h2 class="font-bold text-[#0F2A52]">Existing attachments</h2>
      @forelse($files as $file)
        <label class="flex items-center gap-3 border rounded-xl p-3 text-sm">
          <input type="checkbox" name="remove_files[]" value="{{ $file->id }}" @checked(in_array($file->id, old('remove_files', [])))>
          <span>Remove {{ $file->original_name }}</span>
        </label>
      @empty
        <p class="text-sm text-slate-500">No attachments.</p>
      @endforelse
    </div>
    <label class="block text-sm font-semibold">Add attachments (PDF/images, 10 MB each, 8 total)
      <input type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp" class="block mt-2 w-full text-sm">
    </label>
    <div class="flex justify-end"><button type="submit" class="rounded-xl bg-[#0F2A52] text-white font-semibold px-6 py-3">Save draft changes</button></div>
  </form>
</div>
@endsection
