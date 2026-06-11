<?php

namespace App\Http\Controllers;

use App\Models\PicNbp;
use Illuminate\Http\Request;

class PicNbpController extends Controller
{
    public function index()
    {
        $records = PicNbp::orderBy('nama_petugas')->get();
        return view('pic_nbp.index', compact('records'));
    }

    public function create()
    {
        return view('pic_nbp.form', ['record' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_petugas'     => 'required|string|max:255',
            'jabatan'          => 'nullable|string|max:255',
            'nomor_substitusi' => 'nullable|string|max:255',
            'tanggal_substitusi' => 'nullable|string|max:50',
        ]);

        PicNbp::create($data);

        return redirect()->route('pic_nbp.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(PicNbp $picNbp)
    {
        return view('pic_nbp.form', ['record' => $picNbp]);
    }

    public function update(Request $request, PicNbp $picNbp)
    {
        $data = $request->validate([
            'nama_petugas'     => 'required|string|max:255',
            'jabatan'          => 'nullable|string|max:255',
            'nomor_substitusi' => 'nullable|string|max:255',
            'tanggal_substitusi' => 'nullable|string|max:50',
        ]);

        $picNbp->update($data);

        return redirect()->route('pic_nbp.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(PicNbp $picNbp)
    {
        $picNbp->delete();
        return redirect()->route('pic_nbp.index')->with('success', 'Data berhasil dihapus.');
    }
}
