<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AbsensiHarian;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    private function title(): string { return 'Siswa'; }
    private function routePrefix(): string { return 'siswa'; }

    private function fields(): array
    {
        return [
            'nama_siswa' => ['label' => 'Nama Siswa', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'jenis_kelamin' => ['label' => 'Jenis Kelamin', 'type' => 'select', 'options' => 'jk', 'rules' => 'required|in:L,P'],
            'template_sidik_jari' => ['label' => 'Template Sidik Jari', 'type' => 'textarea', 'rules' => 'nullable|string'],
            'finger_id' => ['label' => 'Finger ID (dari sensor)', 'type' => 'text', 'rules' => 'nullable|integer|unique:siswa,finger_id'],
            'nama_orang_tua' => ['label' => 'Nama Orang Tua', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            'no_telepon_orang_tua' => ['label' => 'No. Telepon Orang Tua', 'type' => 'text', 'rules' => 'nullable|string|max:20'],
            'kelas_id' => ['label' => 'Kelas', 'type' => 'select', 'options' => 'kelas_list', 'rules' => 'required|exists:kelas,id'],
        ];
    }

    private function columns(): array
    {
        return ['Nama Siswa', 'JK', 'Kelas', 'Finger ID'];
    }

    private function options(string $key): array
    {
        return match ($key) {
            'jk' => [ ['value' => 'L', 'label' => 'Laki-laki'], ['value' => 'P', 'label' => 'Perempuan'] ],
            'kelas_list' => Kelas::orderBy('nama_kelas')->get(['id','nama_kelas'])->map(fn($k)=>['value'=>$k->id,'label'=>$k->nama_kelas])->toArray(),
            default => [],
        };
    }

    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => 'Pilihan :attribute tidak valid.',
            'exists' => ':attribute tidak ditemukan.',
            'integer' => ':attribute harus berupa angka.',
            'unique' => ':attribute sudah digunakan.',
        ];
    }

    private function validationAttributes(): array
    {
        return collect($this->fields())->mapWithKeys(fn($def,$name)=>[
            $name => $def['label'] ?? ucfirst(str_replace('_',' ', $name))
        ])->toArray();
    }

    private function buildFields(array $fields, $item = null): array
    {
        $out = [];
        foreach ($fields as $name => $def) {
            $field = $def;
            $field['name'] = $name;
            $field['value'] = old($name, $item->{$name} ?? null);
            if (($def['type'] ?? null) === 'select') {
                $key = $def['options'] ?? null;
                $field['options'] = $key ? $this->options($key) : [];
            }
            $out[] = $field;
        }
        return $out;
    }

    public function index()
    {
        $items = Siswa::with('kelas')->latest()->paginate(10);
        $rows = $items->getCollection()->map(function($item){
            return [
                'id' => $item->id,
                'cols' => [
                    $item->nama_siswa,
                    $item->jenis_kelamin,
                    optional($item->kelas)->nama_kelas ?? '-',
                    $item->finger_id ?? '-',
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
        $rules = collect($this->fields())->mapWithKeys(fn($v,$k)=>[$k=>$v['rules']??''])->filter()->toArray();
        $data = $request->validate($rules, $this->validationMessages(), $this->validationAttributes());
        $model = new Siswa();
        foreach ($this->fields() as $name => $_) {
            $model->{$name} = $data[$name] ?? null;
        }
        $model->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil ditambahkan');
    }

    public function show(Siswa $siswa)
    {
        return view('crud.show', [
            'title' => 'Detail ' . $this->title(),
            'page_title' => 'Detail ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'item' => $siswa,
            'fields' => $this->buildFields($this->fields(), $siswa),
        ]);
    }

    public function edit(Siswa $siswa)
    {
        $fields = $this->buildFields($this->fields(), $siswa);
        return view('crud.form', [
            'title' => 'Ubah ' . $this->title(),
            'page_title' => 'Ubah ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'mode' => 'edit',
            'fields' => $fields,
            'action' => route($this->routePrefix() . '.update', $siswa->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $rules = collect($this->fields())->mapWithKeys(fn($v,$k)=>[$k=>$v['rules']??''])->filter()->toArray();
        // Sesuaikan unik finger_id untuk mengabaikan ID saat update
        if (isset($rules['finger_id'])) {
            $rules['finger_id'] = 'nullable|integer|unique:siswa,finger_id,' . $siswa->id;
        }
        $data = $request->validate($rules, $this->validationMessages(), $this->validationAttributes());
        foreach ($this->fields() as $name => $_) {
            $siswa->{$name} = $data[$name] ?? null;
        }
        $siswa->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil diperbarui');
    }

    public function destroy(Siswa $siswa)
    {
        // Cegah penghapusan jika siswa memiliki riwayat absensi (FK restrict)
        if (AbsensiHarian::where('siswa_id', $siswa->id)->exists()) {
            return redirect()->route($this->routePrefix().'.index')
                ->with('error', 'Tidak dapat menghapus siswa karena memiliki riwayat absensi. Hapus data absensi terlebih dahulu.');
        }

        $siswa->delete();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil dihapus');
    }
}
