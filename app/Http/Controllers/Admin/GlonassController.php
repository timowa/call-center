<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Inertia\Inertia;

class GlonassController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Glonass', [
            'adminLogin' => Auth::guard('admin')->user()->login,
            'scenarios' => DB::table('training_scenarios')->where('kind', 'glonass')->where('active', true)->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/GlonassEdit', ['adminLogin' => Auth::guard('admin')->user()->login, 'scenario' => null]);
    }

    public function edit(int $id)
    {
        $this->find($id);
        $row = DB::table('training_scenarios')->find($id);
        return Inertia::render('Admin/GlonassEdit', ['adminLogin' => Auth::guard('admin')->user()->login,
            'scenario' => ['id' => $row->id, 'name' => $row->name, 'fields' => collect(json_decode($row->content, true, 512, JSON_THROW_ON_ERROR))->map(fn ($value, $key) => ['label' => (string) $key, 'value' => (string) $value])->values()]]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::table('training_scenarios')->insert($data + ['kind' => 'glonass', 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('admin.glonass.index');
    }

    public function update(Request $request, int $id)
    {
        $this->find($id);
        DB::table('training_scenarios')->where('id', $id)->update($this->validated($request) + ['updated_at' => now()]);
        return redirect()->route('admin.glonass.index');
    }

    public function destroy(int $id)
    {
        $this->find($id);
        // Сохраняем связь с уже созданными учебными сессиями.
        DB::table('training_scenarios')->where('id', $id)->update(['active' => false, 'updated_at' => now()]);
        return redirect()->route('admin.glonass.index');
    }

    private function find(int $id): void
    {
        abort_unless(DB::table('training_scenarios')->where('id', $id)->where('kind', 'glonass')->where('active', true)->exists(), 404);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'required|array|min:1|max:100',
            'fields.*' => 'required|array:label,value',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.value' => 'nullable|string|max:2000',
        ], ['name.required' => 'Укажите название шаблона.', 'fields.required' => 'Добавьте хотя бы одно поле.',
            'fields.*.label.required' => 'Укажите название поля.']);
        $content = [];
        foreach ($data['fields'] as $index => $field) {
            $label = trim($field['label']);
            if (array_key_exists($label, $content)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "fields.$index.label" => 'Названия полей не должны повторяться.',
                ]);
            }
            $content[$label] = $field['value'] ?? '';
        }
        return ['name' => $data['name'], 'content' => json_encode((object) $content, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)];
    }
}
