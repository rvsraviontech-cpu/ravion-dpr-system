<?php

namespace App\Framework\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Framework\Traits\MasterAuditTrait;
use App\Framework\Services\MasterQueryService;

abstract class BaseMasterController extends Controller
{
    use MasterAuditTrait;

    protected string $model;

    protected string $view;

    protected string $module;

    protected string $entity;

    protected string $nameField = 'name';

    protected array $searchColumns = [];

    protected array $filters = [
        'status' => 'is_active',
    ];

    protected array $orderBy = [];

    public function __construct(
        protected MasterQueryService $queryService
    ) {
    }

    public function index(Request $request)
    {
        $records = $this->queryService->search(
            model: $this->model,
            request: $request,
            searchColumns: $this->searchColumns,
            filters: $this->filters,
            orderBy: $this->orderBy
        );

        return view("{$this->view}.index", [
            $this->recordsVariable() => $records,
        ]);
    }

    public function create()
    {
        return view("{$this->view}.create", $this->formData());
    }

    public function show($id)
    {
        $record = $this->model::findOrFail($id);

        return view("{$this->view}.show", [
            $this->recordVariable() => $record,
        ]);
    }

    public function edit($id)
    {
        $record = $this->model::findOrFail($id);

        return view("{$this->view}.edit", array_merge(
            [
                $this->recordVariable() => $record,
            ],
            $this->formData()
        ));
    }

    protected function formData(): array
    {
        return [];
    }

    protected function recordVariable(): string
    {
        return lcfirst(class_basename($this->model));
    }

    protected function recordsVariable(): string
    {
        return $this->recordVariable() . 's';
    }
}