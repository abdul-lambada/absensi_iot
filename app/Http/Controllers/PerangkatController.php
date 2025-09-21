<?php

namespace App\Http\Controllers;

use App\Models\Perangkat;
use Illuminate\Http\Request;

class PerangkatController extends Controller
{
    private function title(): string { return 'Perangkat'; }
    private function routePrefix(): string { return 'perangkat'; }

    private function fields(): array
    {
        return [
            'nama_perangkat' => ['label' => 'Nama Perangkat', 'type' => 'text', 'rules' => 'required|string|max:255'],
            'lokasi_perangkat' => ['label' => 'Lokasi Perangkat', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
            'status_perangkat' => ['label' => 'Status', 'type' => 'select', 'options' => 'status_opts', 'rules' => 'required|in:aktif,nonaktif'],
            'device_uid' => ['label' => 'Device UID', 'type' => 'text', 'rules' => 'required|string|max:100|unique:perangkat,device_uid'],
            'api_key' => ['label' => 'API Key', 'type' => 'password', 'rules' => 'required|string|max:100|unique:perangkat,api_key'],
        ];
    }

    private function columns(): array
    {
        return ['Nama Perangkat', 'Lokasi', 'Status', 'Device UID'];
    }

    private function options(string $key): array
    {
        return match ($key) {
            'status_opts' => [ ['value' => 'aktif', 'label' => 'Aktif'], ['value' => 'nonaktif', 'label' => 'Nonaktif'] ],
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
            if (($def['type'] ?? null) === 'select') {
                $key = $def['options'] ?? null;
                $field['options'] = $key ? $this->options($key) : [];
            }
            $out[] = $field;
        }
        return $out;
    }

    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max' => ':attribute maksimal :max karakter.',
            'in' => 'Pilihan :attribute tidak valid.',
            'unique' => ':attribute sudah digunakan.',
        ];
    }

    private function validationAttributes(): array
    {
        return collect($this->fields())->mapWithKeys(fn($def,$name)=>[
            $name => $def['label'] ?? ucfirst(str_replace('_',' ', $name))
        ])->toArray();
    }

    private function maskSecret(?string $value): string
    {
        if (!$value) return '-';
        $last4 = substr($value, -4);
        return '********' . $last4;
    }

    public function index()
    {
        $items = Perangkat::latest()->paginate(10);
        $rows = $items->getCollection()->map(function($item){
            return [
                'id' => $item->id,
                'cols' => [$item->nama_perangkat, $item->lokasi_perangkat, $item->status_perangkat, $item->device_uid],
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
        $model = new Perangkat();
        foreach ($this->fields() as $name => $_) {
            $model->{$name} = $data[$name] ?? null;
        }
        $model->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil ditambahkan');
    }

    public function show(Perangkat $perangkat)
    {
        $fields = $this->buildFields($this->fields(), $perangkat);
        // Mask API Key hanya pada tampilan detail
        $fields = array_map(function($f){
            if (($f['name'] ?? null) === 'api_key') {
                $f['value'] = $this->maskSecret($f['value'] ?? null);
            }
            return $f;
        }, $fields);

        return view('crud.show', [
            'title' => 'Detail ' . $this->title(),
            'page_title' => 'Detail ' . $this->title(),
            'routePrefix' => $this->routePrefix(),
            'item' => $perangkat,
            'fields' => $fields,
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
        if (isset($rules['device_uid'])) {
            $rules['device_uid'] = 'required|string|max:100|unique:perangkat,device_uid,' . $perangkat->id;
        }
        if (isset($rules['api_key'])) {
            // Pada edit, API Key bersifat opsional dan unik bila diisi
            $rules['api_key'] = 'nullable|string|max:100|unique:perangkat,api_key,' . $perangkat->id;
        }
        $data = $request->validate($rules, $this->validationMessages(), $this->validationAttributes());

        foreach ($this->fields() as $name => $_) {
            if ($name === 'api_key') {
                // Jangan overwrite jika kosong (user tidak ingin mengubah)
                if (isset($data['api_key']) && $data['api_key'] !== null && $data['api_key'] !== '') {
                    $perangkat->api_key = $data['api_key'];
                }
            } else {
                $perangkat->{$name} = $data[$name] ?? null;
            }
        }
        $perangkat->save();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil diperbarui');
    }

    public function destroy(Perangkat $perangkat)
    {
        $perangkat->delete();
        return redirect()->route($this->routePrefix().'.index')->with('success', $this->title().' berhasil dihapus');
    }
}
