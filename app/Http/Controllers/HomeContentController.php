<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\HomeContent;

class HomeContentController extends Controller
{
    /**
     * Mostrar lista de contenidos de la home
     */
    public function index()
    {
        $contents = HomeContent::orderBy('section')->get();
        return view('admin.home_contents.index')->with(['contents' => $contents]);
    }

    /**
     * Obtener lista de contenidos para DataTables (AJAX)
     */
    public function getList()
    {
        $contents = HomeContent::orderBy('sort_order')->get();

        $data = [];
        foreach ($contents as $content) {
            $data[] = [
                'id' => $content->id,
                'section' => $content->section,
                'title' => $content->title,
                'subtitle' => $content->subtitle,
                'is_active' => $content->is_active,
                'sort_order' => $content->sort_order,
                'created_at' => $content->created_at->format('d/m/Y H:i')
            ];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Mostrar formulario para crear nuevo contenido
     */
    public function create()
    {
        return view('admin.home_contents.create');
    }

    /**
     * Guardar nuevo contenido
     */
    public function store(Request $request)
    {
        $rules = [
            'section' => 'required|string|max:100|unique:home_contents',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ];

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        HomeContent::create([
            'section' => $request->input('section'),
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'content' => $request->input('content'),
            'link_text' => $request->input('link_text'),
            'link_url' => $request->input('link_url'),
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->input('sort_order', 0)
        ]);

        return redirect('/admin/home-contents')->with(['status' => 'success', 'message' => 'Contenido creado correctamente.', 'icon' => 'fa-check']);
    }

    /**
     * Mostrar detalle de un contenido
     */
    public function show($id)
    {
        $content = HomeContent::find($id);

        if (!$content) {
            return redirect('/admin/home-contents')->with(['status' => 'danger', 'message' => 'Contenido no encontrado.', 'icon' => 'fa-frown-o']);
        }

        return view('admin.home_contents.show')->with(['content' => $content]);
    }

    /**
     * Mostrar formulario para editar contenido
     */
    public function edit($id)
    {
        $content = HomeContent::find($id);

        if (!$content) {
            return redirect('/admin/home-contents')->with(['status' => 'danger', 'message' => 'Contenido no encontrado.', 'icon' => 'fa-frown-o']);
        }

        return view('admin.home_contents.edit')->with(['content' => $content]);
    }

    /**
     * Actualizar contenido
     */
    public function update(Request $request, $id)
    {
        $content = HomeContent::find($id);

        if (!$content) {
            return redirect('/admin/home-contents')->with(['status' => 'danger', 'message' => 'Contenido no encontrado.', 'icon' => 'fa-frown-o']);
        }

        $rules = [
            'section' => 'required|string|max:100|unique:home_contents,section,' . $id,
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer'
        ];

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        $content->update([
            'section' => $request->input('section'),
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'content' => $request->input('content'),
            'link_text' => $request->input('link_text'),
            'link_url' => $request->input('link_url'),
            'is_active' => $request->has('is_active'),
            'sort_order' => $request->input('sort_order', 0)
        ]);

        return redirect('/admin/home-contents')->with(['status' => 'success', 'message' => 'Contenido actualizado correctamente.', 'icon' => 'fa-check']);
    }

    /**
     * Eliminar contenido
     */
    public function destroy($id)
    {
        $content = HomeContent::find($id);

        if (!$content) {
            return response()->json(['error' => 'Contenido no encontrado'], 404);
        }



        $content->delete();

        return response()->json(['success' => 'Contenido eliminado correctamente']);
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActive($id)
    {
        $content = HomeContent::find($id);

        if (!$content) {
            return response()->json(['error' => 'Contenido no encontrado'], 404);
        }

        $content->update(['is_active' => !$content->is_active]);

        $status = $content->is_active ? 'activado' : 'desactivado';

        return response()->json([
            'success' => 'Contenido ' . $status . ' correctamente',
            'is_active' => $content->is_active
        ]);
    }
}