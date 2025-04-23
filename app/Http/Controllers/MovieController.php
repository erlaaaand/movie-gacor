<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use App\Http\Requests\StoreMovieRequest;

class MovieController extends Controller
{
    public function index()
    {
        $query = Movie::latest();
        if (request('search')) {
            $query->where('judul', 'like', '%' . request('search') . '%')
                ->orWhere('sinopsis', 'like', '%' . request('search') . '%');
        }
        $movies = $query->paginate(6)->withQueryString();
        return view('homepage', compact('movies'));
    }

    public function detail($id)
    {
        $movie = Movie::findOrFail($id);
        return view('detail', compact('movie'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('input', compact('categories'));
    }

    public function store(StoreMovieRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('foto_sampul')) {
            $validated['foto_sampul'] = $request->file('foto_sampul')->store('movie_covers', 'public');
        }

        Movie::create($validated);

        return redirect('/')->with('success', 'Film Berhasil Di Tambahkan');
    }

    public function data()
    {
        $movies = Movie::latest()->paginate(10);
        return view('data-movies', compact('movies'));
    }

    public function form_edit($id)
    {
        $movie = Movie::findOrFail($id);
        $categories = Category::all();
        return view('form-edit', compact('movie', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sinopsis' => 'required|string',
            'tahun' => 'required|integer|min:1900|max:' . date('Y'),
            'pemain' => 'required|string',
            'foto_sampul' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $movie = Movie::findOrFail($id);

        if ($request->hasFile('foto_sampul')) {
            if ($movie->foto_sampul) {
                Storage::disk('public')->delete($movie->foto_sampul);
            }
            $filePath = $request->file('foto_sampul')->store('movie_covers', 'public');
            $movie->foto_sampul = $filePath;
        }

        $movie->update($request->only(['judul', 'sinopsis', 'category_id', 'tahun', 'pemain', 'foto_sampul']));

        return redirect()->route('movies.data')->with('success', 'Data berhasil diperbarui');
    }

    public function delete($id)
    {
        $movie = Movie::findOrFail($id);

        if ($movie->foto_sampul) {
            Storage::disk('public')->delete($movie->foto_sampul);
        }

        $movie->delete();

        return redirect()->route('movies.data')->with('success', 'Data berhasil dihapus');
    }

    public function getPopularMovies()
    {
        $movies = Movie::with('reviews')->where('is_popular', true)->get();

        return response()->json($movies->map(function ($movie) {
            return [
                'title' => $movie->title,
                'rating' => $movie->average_rating,
            ];
        }));
    }
}
