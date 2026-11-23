<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Inertia\Inertia;

class SmsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Sms', [
            'adminLogin' => Auth::guard('admin')->user()->login,
            'messages' => DB::table('training_scenarios')->where('kind', 'sms')->where('active', true)->orderBy('id')->get(['id', 'name', 'content'])->map(fn ($row) => ['id' => $row->id, 'name' => $row->name, 'count' => count(preg_split('/\R/u', trim($row->content)))]),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/SmsEdit', ['adminLogin' => Auth::guard('admin')->user()->login, 'scenario' => null]);
    }

    public function edit(int $id)
    {
        $this->find($id);
        $row = DB::table('training_scenarios')->find($id);
        return Inertia::render('Admin/SmsEdit', ['adminLogin' => Auth::guard('admin')->user()->login,
            'scenario' => ['id' => $row->id, 'name' => $row->name, 'messages' => preg_split('/\R/u', trim($row->content))]]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::table('training_scenarios')->insert($data + ['kind' => 'sms', 'active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return redirect()->route('admin.sms.index');
    }

    public function update(Request $request, int $id)
    {
        $this->find($id);
        DB::table('training_scenarios')->where('id', $id)->update($this->validated($request) + ['updated_at' => now()]);
        return redirect()->route('admin.sms.index');
    }

    public function destroy(int $id)
    {
        $this->find($id);
        // Сохраняем связь с уже созданными учебными сессиями.
        DB::table('training_scenarios')->where('id', $id)->update(['active' => false, 'updated_at' => now()]);
        return redirect()->route('admin.sms.index');
    }

    private function find(int $id): void
    {
        abort_unless(DB::table('training_scenarios')->where('id', $id)->where('kind', 'sms')->where('active', true)->exists(), 404);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'messages' => 'required|array|min:1|max:100',
            'messages.*' => ['required', 'string', 'max:2000', 'not_regex:/[\r\n]/'],
        ], ['name.required' => 'Укажите тему обращения.', 'messages.required' => 'Добавьте хотя бы одно СМС.',
            'messages.*.required' => 'Введите текст СМС.', 'messages.*.not_regex' => 'Каждое СМС должно быть в отдельном поле.']);
        return ['name' => $data['name'], 'content' => implode("\n", $data['messages'])];
    }
}
