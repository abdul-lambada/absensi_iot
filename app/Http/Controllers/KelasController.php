<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    private function title(): string { return 'Kelas'; }
    private function routePrefix(): string { return 'kelas'; }

    private function fields(): array
    {
        return [
            'nama_kelas' => ['label' => 'Nama Kelas', 'type' => 'text', 'rules' => 'required|string|max:50'],
            'tahun_ajaran' => ['label' => 'Tahun Ajaran', 'type' => 'text', 'rules' => 'required|string|max:10'],
            'guru' => ['label' => 'Wali Kelas (Guru)', 'type' => 'select', 'options' => 'gurus', 'rules' => 'required|exists:users,id'],
        ];
    }

    private function columns(): array
    {
        return ['Nama Kelas', 'Tahun Ajaran', 'Wali Kelas'];
    }

    private function options(string $key): array
    {
        return match ($key) {
            'gurus' => User::where('role', 'guru')->orderBy('nama_lengkap')->get(['id','nama_lengkap'])->map(fn($u)=>['value'=>$u->id,'label'=>$u->nama_lengkap])->toArray(),
            default => [],
        };
    }

    private function buildFields(array $fields, $item = null): array
    {
        $out = [];
        foreach ($fields as $name => $def) {
            $field = $def;
            $field['name'] = $name;
            $field['value'] = old($name, $item->{$name} ?? null);
            if (($field['type'] ?? '') === 'select' && ($field['options'] ?? null) === 'gurus') {
                $field['options'] = $this->options('gurus');
            }
            $out[] = $field;
        }
        return $out;
    }

    public function index()
    {
        $items = Kelas::latest()->paginate(10);
        $rows = $items->getCollection()->map(function($item){
            $guru = User::find($item->guru);
            return [
                'id' => $item->id,
                'cols' => [$item->nama_kelas, $item->tahun_ajaran, $guru->nama_lengkap ?? '-'],
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
        $rules = collect($this->fields())->mapWithKeys(fn($v,$k)=>[$k=>$v['rules']??''])->filter()->toArray();
        $data = $request->validate($rules);
        $model = new Kelas();
        foreach ($this->fields() as $name => $_) {
            $model->{$name} = $data[$name] ?? null;
        }
        $model->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil ditambahkan');
    }

    public function show(Kelas $kelas)
    {
        return view('crud.show', [
            'title' => 'Detail ' . $this->title(),
            'page_title' => 'Detail ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'item' => $kelas,
            'fields' => $this->buildFields($this->fields(), $kelas),
        ]);
    }

    public function edit(Kelas $kelas)
    {
        $fields = $this->buildFields($this->fields(), $kelas);
        return view('crud.form', [
            'title' => 'Ubah ' . $this->title(),
            'page_title' => 'Ubah ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'mode' => 'edit',
            'fields' => $fields,
            'action' => route($this->routePrefix() . '.update', $kelas->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $rules = collect($this->fields())->mapWithKeys(fn($v,$k)=>[$k=>$v['rules']??''])->filter()->toArray();
        $data = $request->validate($rules);
        foreach ($this->fields() as $name => $_) {
            $kelas->{$name} = $data[$name] ?? null;
        }
        $kelas->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil diperbarui');
    }

    public function destroy(Kelas $kelas)
    {
        // Cegah penghapusan jika masih ada siswa terkait untuk menghindari error FK
        if ($kelas->siswa()->exists()) {
            return redirect()->route($this->routePrefix().'.index')
                ->with('error', 'Tidak dapat menghapus kelas karena masih memiliki siswa. Pindahkan atau hapus siswa terlebih dahulu.');
        }

        $kelas->delete();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil dihapus');
    }
}
