<div>
    <h2 class="text-sm font-bold text-gray-800 mb-3">Remarks</h2>

    <textarea name="remarks"
              rows="5"
              class="{{ $textarea }}"
              placeholder="Project remarks, important notes, internal planning notes...">{{ old('remarks', optional($project)->remarks) }}</textarea>
</div>