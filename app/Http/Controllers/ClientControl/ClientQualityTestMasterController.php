<?php

namespace App\Http\Controllers\ClientControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientQualityTestMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('client_quality_test_masters');
        if ($request->filled('q')) {
            $term = trim((string) $request->query('q'));
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%");
            });
        }
        if ($request->filled('category')) $query->where('category', $request->query('category'));
        if ($request->query('status') === 'active') $query->where('is_active', true);
        if ($request->query('status') === 'archived') $query->where('is_active', false);
        $tests = $query->orderBy('category')->orderBy('name')->paginate(25)->withQueryString();
        $categories = DB::table('client_quality_test_masters')->distinct()->orderBy('category')->pluck('category');
        return view('client-control.admin.quality.tests.index', compact('tests', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = trim($data['code']);
        $data['requires_evidence'] = $request->boolean('requires_evidence');
        $data['is_active'] = true;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();
        DB::table('client_quality_test_masters')->insert($data);
        return back()->with('success', 'Quality test created.');
    }

    public function update(Request $request, int $test)
    {
        $existing = DB::table('client_quality_test_masters')->where('id', $test)->first();
        abort_unless($existing, 404);
        $data = $this->validated($request, $test);
        $data['requires_evidence'] = $request->boolean('requires_evidence');
        $data['updated_by'] = auth()->id();
        $data['updated_at'] = now();
        DB::table('client_quality_test_masters')->where('id', $test)->update($data);
        return back()->with('success', 'Quality test updated.');
    }

    public function status(Request $request, int $test)
    {
        $existing = DB::table('client_quality_test_masters')->where('id', $test)->first();
        abort_unless($existing, 404);
        DB::table('client_quality_test_masters')->where('id', $test)->update([
            'is_active' => !$existing->is_active,
            'archived_at' => $existing->is_active ? now() : null,
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);
        return back()->with('success', $existing->is_active ? 'Test archived.' : 'Test reactivated.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:24', Rule::unique('client_quality_test_masters', 'code')->ignore($id)],
            'category' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::in(['Laboratory', 'Field', 'Inspection'])],
            'description' => ['nullable', 'string'],
            'reference_standard' => ['nullable', 'string', 'max:255'],
            'acceptance_criteria' => ['nullable', 'string'],
            'default_frequency' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
