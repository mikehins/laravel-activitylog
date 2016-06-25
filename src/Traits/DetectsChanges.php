<?php

namespace Spatie\Activitylog\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait DetectsChanges
{
    protected $oldValues = [];

    protected $newValues = [];

    protected static function bootDetectsChanges()
    {
        collect(['updating', 'deleting'])->each(function ($eventName) {

            return static::$eventName(function (Model $model) {

                $model->oldValues = $model->fresh()->toArray();

                $model->newValues = $model->getDirty();

            });
        });
    }

    public function getChangedAttributeNames(): array
    {
        return array_keys(array_intersect_key($this->oldValues, $this->newValues));
    }

    public function getChangedValues(): Collection
    {
        if (!isset($this->logChangesOnAttributes)) {
            return collect();
        }

        $changes = collect($this->getChangedAttributeNames())
            ->filter(function (string $attributeName) {
               return collect($this->logChangesOnAttributes)->contains($attributeName);
            })
            ->reduce(function (array $changes, string $changedAttributeName) {
                $changes['old'][$changedAttributeName] = $this->oldValues[$changedAttributeName];

                $changes['new'][$changedAttributeName] = $this->newValues[$changedAttributeName];

                return $changes;
            }, ['old' => [], 'new' => []]);

        return collect($changes);
    }
}