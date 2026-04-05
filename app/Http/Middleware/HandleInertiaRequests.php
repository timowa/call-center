<?php

namespace App\Http\Middleware;

use App\Condition;
use App\Models\Area;
use App\Models\CallType;
use App\Models\District;
use App\Models\EmergencyType;
use App\Models\IncidentType;
use App\Models\Service;
use App\SourceType;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'uid' => $user->uid,
                    'area_id' => $user->area_id,
                    'call_type_id' => $user->call_type_id,
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'roles' => $user->getRoleNames()
                ] : null,
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'type' => fn () => $request->session()->get('type'),
                'isNewIncident' => fn () => $request->session()->get('isNewIncident')
            ],
            'dictionaries' => [
                'conditions' => collect(Condition::cases())->mapWithKeys(fn($s) => [
                    $s->value => ['name' => $s->label(), 'color' => $s->color(), 'id' => $s->value],
                ]),
                'sources' => collect(SourceType::cases())->map(function ($item) {
                    return ['id' => $item->value, 'name' => $item->label()];
                })->toArray(),

            ],
            'constants' => [
                'sources' => collect(SourceType::cases())->pluck('value', 'name')->toArray(),
            ],
            'directories' => [
                'services'       => Service::all(),
                'callTypes'      => CallType::all(),
                'incidentTypes'  => IncidentType::all(),
                'emergencyTypes' => EmergencyType::all(),
                'areas'          => Area::all(),
                'districts'      => District::all(),
            ]

        ];
    }
}
