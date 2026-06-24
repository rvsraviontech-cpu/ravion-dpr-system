<div>
    <h2 class="text-lg font-bold text-gray-800 mb-4">Project Team</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="{{ $label }}">Assigned PMO / DGM</label>
            <select name="assigned_pmo_id" class="{{ $input }}">
                <option value="">Select PMO / DGM</option>
                @foreach($pmoUsers as $user)
                    <option value="{{ $user->id }}"
                        {{ old('assigned_pmo_id', optional($project)->assigned_pmo_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="{{ $label }}">Assigned Site Engineers</label>

            <div class="border rounded-md p-3 max-h-64 overflow-y-auto bg-gray-50 space-y-2">
                @foreach($engineers as $engineer)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox"
                               name="engineers[]"
                               value="{{ $engineer->id }}"
                               class="rounded border-gray-300"
                               {{ $project && $project->users->contains($engineer->id) ? 'checked' : '' }}>

                        <span>{{ $engineer->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>