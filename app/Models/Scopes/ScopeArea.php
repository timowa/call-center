<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopeArea implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (!$user) {
            return;
        }
        if ($user->area_id && $user->hasPermissionTo('incidents.view.own-area')) {
            $builder->whereHas('district', function ($query) use ($user) {
                $query->where('area_id', $user->area_id);
            });
        }
    }
}
