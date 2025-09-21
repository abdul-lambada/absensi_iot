<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private function title(): string { return 'Pengguna'; }
    private function routePrefix(): string { return 'users'; }

    private function fields(): array
    {
        return [
            'nama_lengkap' => ['label' => 'Nama Lengkap', 'type' => 'text'],
            'username' => ['label' => 'Username', 'type' => 'text'],
            'password' => ['label' => 'Password', 'type' => 'password'],
            'role' => ['label' => 'Peran', 'type' => 'select', 'options' => 'roles'],
            'no_telepon' => ['label' => 'No. Telepon', 'type' => 'text'],
        ];
    }

    private function columns(): array
    {
        return ['Nama Lengkap', 'Username', 'Peran', 'No. Telepon'];
    }

    private function options(string $key): array
    {
        return match ($key) {
            'roles' => [
                ['value' => 'admin', 'label' => 'Admin'],
                ['value' => 'guru', 'label' => 'Guru'],
                ['value' => 'kepala_sekolah', 'label' => 'Kepala Sekolah'],
            ],
            default => [],
        };
    }

    private function buildFields(array $fields, $item = null, array $overrides = []): array
    {
        $out = [];
        foreach ($fields as $name => $def) {
            $field = $def;
            $field['name'] = $name;
            // Jangan pernah prefill password
            if ($name === 'password') {
                $field['value'] = '';
            } else {
                $field['value'] = old($name, $item->{$name} ?? null);
            }
            if (($def['type'] ?? null) === 'select') {
                $key = $def['options'] ?? null;
                $field['options'] = $key ? $this->options($key) : [];
            }
            if (isset($overrides[$name]) && is_array($overrides[$name])) {
                $field = array_merge($field, $overrides[$name]);
            }
            $out[] = $field;
        }
        return $out;
    }

    public function index()
    {
        $items = User::latest()->paginate(10);
        $rows = $items->getCollection()->map(function($item){
            return [
                'id' => $item->id,
                'cols' => [
                    $item->nama_lengkap,
                    $item->username,
                    ucfirst(str_replace('_',' ', $item->role)),
                    $item->no_telepon ?? '-',
                ],
            ];
        });

        return view('crud.index', [
            'title' => 'Kelola ' . $this->title(),
            'page_title' => 'Kelola ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'headers' => $this->columns(),
            'items' => $items,
            'rows' => $rows,
        ]);
    }

    public function create()
    {
        $fields = $this->buildFields($this->fields());
        return view('crud.form', [
            'title' => 'Tambah ' . $this->title(),
            'page_title' => 'Tambah ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'mode' => 'create',
            'fields' => $fields,
            'action' => route($this->routePrefix() . '.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required','string','max:255'],
            'username' => ['required','string','max:100','unique:users,username'],
            'password' => ['required','string','min:6'],
            'role' => ['required', Rule::in(['admin','guru','kepala_sekolah'])],
            'no_telepon' => ['nullable','string','max:20'],
        ]);

        $user = new User();
        $user->nama_lengkap = $data['nama_lengkap'];
        $user->username = $data['username'];
        // cast 'hashed' pada model akan meng-hash otomatis
        $user->password_hash = $data['password'];
        $user->role = $data['role'];
        $user->no_telepon = $data['no_telepon'] ?? null;
        $user->save();

        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil ditambahkan');
    }

    public function show(User $user)
    {
        // Sembunyikan field password pada halaman detail
        $fields = $this->fields();
        unset($fields['password']);
        return view('crud.show', [
            'title' => 'Detail ' . $this->title(),
            'page_title' => 'Detail ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'item' => $user,
            'fields' => $this->buildFields($fields, $user),
        ]);
    }

    public function edit(User $user)
    {
        $fields = $this->buildFields($this->fields(), $user, [
            'password' => ['label' => 'Password (kosongkan jika tidak diubah)']
        ]);
        return view('crud.form', [
            'title' => 'Ubah ' . $this->title(),
            'page_title' => 'Ubah ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'mode' => 'edit',
            'fields' => $fields,
            'action' => route($this->routePrefix() . '.update', $user->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required','string','max:255'],
            'username' => ['required','string','max:100', Rule::unique('users','username')->ignore($user->id)],
            'password' => ['nullable','string','min:6'],
            'role' => ['required', Rule::in(['admin','guru','kepala_sekolah'])],
            'no_telepon' => ['nullable','string','max:20'],
        ]);

        $user->nama_lengkap = $data['nama_lengkap'];
        $user->username = $data['username'];
        if (!empty($data['password'])) {
            $user->password_hash = $data['password'];
        }
        $user->role = $data['role'];
        $user->no_telepon = $data['no_telepon'] ?? null;
        $user->save();

        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil dihapus');
    }
}