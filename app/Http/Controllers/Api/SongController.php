<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SongResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Song;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
{
    public function index()
    {
        $songs = Song::latest()->paginate(3);

        return new SongResource(true, 'Data lagu saat ini', $songs);
    }

    public function store(Request $request)
    {
        //cek validasi data
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'nama'      => 'required',
            'judul_lagu' => 'required',
        ]);

        //cek validasi jika gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $imageName = bcrypt(now()) . $image->getClientOriginalName();
        $request->image->move(public_path('siswa-images'), $imageName);

        //buat data song
        $song = Song::create([
            'image'     => $imageName,
            'nama'      => $request->nama,
            'judul_lagu' => $request->judul_lagu
        ]);

        //kirim response
        return new SongResource(true, 'Data berhasil ditambahkan', $song);
    }

    public function show($id)
    {
        $song = Song::find($id);

        return new SongResource(true, 'Detail data lagu', $song);
    }

    public function update(Request $request, $id)
    {
        //define validation rules
        $validator = Validator::make($request->all(), [
            'nama'     => 'required',
            'judul_lagu'   => 'required',
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //find post by ID
        $song = Song::find($id);

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $imageName = bcrypt(now()) . $image->getClientOriginalName();
            $request->image->move(public_path('siswa-images'), $imageName);

            //delete old image
            unlink("siswa-images/" . $song->image);

            //update post with new image
            $song->update([
                'image'         => $imageName,
                'nama'          => $request->nama,
                'judul_lagu'    => $request->judul_lagu,
            ]);
        } else {

            //update post without image
            $song->update([
                'image'         => $song->image,
                'nama'          => $request->nama,
                'judul_lagu'    => $request->judul_lagu,
            ]);
        }

        //return response
        return new SongResource(true, 'Data Lagu Berhasil Diubah!', $song);
    }

    public function destroy($id)
    {
        $song = Song::find($id);
        unlink("siswa-images/" . $song->image);

        $song->delete();
        return new SongResource(true, 'Data lagu berhasil dihapus', null);
    }
}
