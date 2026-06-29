<?php

namespace App\Framework\Services;

use Illuminate\Database\Eloquent\Model;

class MasterCrudService
{
    public function create(string $modelClass, array $data): Model
    {
        return $modelClass::create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    public function toggleStatus(Model $model, string $statusColumn = 'is_active'): Model
    {
        $model->update([
            $statusColumn => !$model->{$statusColumn},
        ]);

        return $model->fresh();
    }

    public function auditValues(Model $model, array $fields): array
    {
        return $model->only($fields);
    }
}