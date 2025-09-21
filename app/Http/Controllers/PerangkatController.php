<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use App\Models\AbsensiHarian;
use Illuminate\Http\Request;

class PerangkatController extends Controller
{
    private function title(): string { return 'Perangkat'; }
    private function routePrefix(): string { return 'perangkat'; }

    private function fields(): array
    {
        return [
            'nama_perangkat' => ['label' => 'Nama Perangkat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'keterangan' => ['label' => 'Keterangan', 'type' => 'textarea', 'rules' => 'nullable|string'],
        ];
    }

    private function columns(): array
    {
        return ['Nama Perangkat', 'Keterangan'];
    }

    private function buildFields(array $fields, $item = null): array
    {
        $out = [];
        foreach ($fields as $name => $def) {
            $field = $def;
            $field['name'] = $name;
            $field['value'] = old($name, $item->{$name} ?? null);
            $out[] = $field;
        }
        return $out;
    }

    public function index()
    {
        $items = Perangkat::latest()->paginate(10);
        $rows = $items->getCollection()->map(function($item){
            return [
                'id' => $item->id,
                'cols' => [$item->nama_perangkat, str($item->keterangan)->limit(40)],
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
        $model = new Perangkat();
        foreach ($this->fields() as $name => $_) {
            $model->{$name} = $data[$name] ?? null;
        }
        $model->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil ditambahkan');
    }

    public function show(Perangkat $perangkat)
    {
        return view('crud.show', [
            'title' => 'Detail ' . $this->title(),
            'page_title' => 'Detail ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'item' => $perangkat,
            'fields' => $this->buildFields($this->fields(), $perangkat),
        ]);
    }

    public function edit(Perangkat $perangkat)
    {
        $fields = $this->buildFields($this->fields(), $perangkat);
        return view('crud.form', [
            'title' => 'Ubah ' . $this->title(),
            'page_title' => 'Ubah ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'mode' => 'edit',
            'fields' => $fields,
            'action' => route($this->routePrefix() . '.update', $perangkat->id),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Perangkat $perangkat)
    {
        $rules = collect($this->fields())->mapWithKeys(fn($v,$k)=>[$k=>$v['rules']??''])->filter()->toArray();
        $data = $request->validate($rules);
        foreach ($this->fields() as $name => $_) {
            $perangkat->{$name} = $data[$name] ?? null;
        }
        $perangkat->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil diperbarui');
    }

    public function destroy(Perangkat $perangkat)
    {
        // Cegah penghapusan jika perangkat dipakai pada absensi (masuk/pulang)
        $usedAsMasuk = AbsensiHarian::where('perangkat_masuk_id', $perangkat->id)->exists();
        $usedAsPulang = AbsensiHarian::where('perangkat_pulang_id', $perangkat->id)->exists();
        if ($usedAsMasuk || $usedAsPulang) {
            return redirect()->route($this->routePrefix().'.index')
                ->with('error', 'Tidak dapat menghapus perangkat karena digunakan pada data absensi. Hapus atau ubah data absensi terkait terlebih dahulu.');
        }

        $perangkat->delete();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil dihapus');
    }
}
