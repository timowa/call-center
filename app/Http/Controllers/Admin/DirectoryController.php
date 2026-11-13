<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminDirectories;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DirectoryController extends Controller
{
    public function index(Request $request, string $directory = 'incident-types')
    {
        $definition = AdminDirectories::get($directory);
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:255'], 'group' => ['nullable', Rule::in($definition['groups'])]]);
        $query = DB::table($definition['table']);
        if ($search = $filters['q'] ?? null) $query->where('name', 'like', '%'.$search.'%');
        return Inertia::render('Admin/Directories', [
            'adminLogin' => Auth::guard('admin')->user()->login,
            'directory' => $directory,
            'definition' => ['label' => $definition['label'], 'area' => $definition['area'] ?? false],
            'navigation' => collect(AdminDirectories::all())->map(fn ($item, $key) => ['key' => $key, 'label' => $item['label'], 'groups' => $item['groups']])->values(),
            'group' => $filters['group'] ?? $definition['groups'][0],
            'search' => $search ?? '',
            'rows' => $query->orderBy('name')->orderBy('id')->paginate(30)->withQueryString(),
            'areas' => DB::table('areas')->orderBy('name')->get(['id', 'name']),
            'notice' => session('admin_notice'),
        ]);
    }

    public function store(Request $request, string $directory)
    {
        $definition = AdminDirectories::get($directory);
        DB::table($definition['table'])->insert($this->validated($request, $definition));
        return back()->with('admin_notice', 'Запись создана. Она доступна при открытии формы карточки.');
    }

    public function update(Request $request, string $directory, int $id)
    {
        $definition = AdminDirectories::get($directory);
        $row = DB::table($definition['table'])->find($id);
        abort_unless($row, 404);
        $data = $this->validated($request, $definition);
        if (isset($data['area_id']) && $data['area_id'] != $row->area_id && AdminDirectories::isUsed($definition, $id)) {
            throw ValidationException::withMessages(['area_id' => 'Нельзя менять район записи, используемой в карточках.']);
        }
        DB::table($definition['table'])->where('id', $id)->update($data);
        return back()->with('admin_notice', 'Изменения сохранены.');
    }

    public function destroy(string $directory, int $id)
    {
        $definition = AdminDirectories::get($directory);
        abort_unless(DB::table($definition['table'])->where('id', $id)->exists(), 404);
        if (AdminDirectories::isUsed($definition, $id)) $this->usedError();
        try {
            DB::table($definition['table'])->where('id', $id)->delete();
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503'], true)) $this->usedError();
            throw $exception;
        }
        return back()->with('admin_notice', 'Запись удалена.');
    }

    private function validated(Request $request, array $definition): array
    {
        $rules = ['name' => ['required', 'string', 'max:255']];
        if ($definition['area'] ?? false) $rules['area_id'] = ['required', 'integer', 'exists:areas,id'];
        return $request->validate($rules, [
            'name.required' => 'Укажите название.', 'name.max' => 'Название не должно превышать 255 символов.',
            'area_id.required' => 'Выберите район.', 'area_id.exists' => 'Выберите существующий район.',
        ]);
    }

    private function usedError(): never
    {
        throw ValidationException::withMessages(['delete' => 'Запись используется в карточках. Удаление невозможно.']);
    }
}
