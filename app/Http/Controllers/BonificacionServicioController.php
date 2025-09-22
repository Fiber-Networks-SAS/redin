<?php

namespace App\Http\Controllers;

use App\BonificacionServicio;
use App\Servicio;
use Illuminate\Http\Request;
use View;
use Validator;

class BonificacionServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $bonificaciones = BonificacionServicio::with('servicio')
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return View::make('admin.bonificaciones.index', compact('bonificaciones'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $servicios = Servicio::where('status', 1)->orderBy('nombre')->get();
        
        return View::make('admin.bonificaciones.create', compact('servicios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:servicios,id',
            'porcentaje_bonificacion' => 'required|numeric|min:0|max:100',
            'periodos_bonificacion' => 'required|integer|min:1|max:120',
            'fecha_inicio' => 'required|date',
            'descripcion' => 'nullable|string|max:1000'
        ], [
            'service_id.required' => 'Debe seleccionar un servicio',
            'service_id.exists' => 'El servicio seleccionado no existe',
            'porcentaje_bonificacion.required' => 'El porcentaje de bonificación es requerido',
            'porcentaje_bonificacion.numeric' => 'El porcentaje debe ser un número',
            'porcentaje_bonificacion.min' => 'El porcentaje no puede ser negativo',
            'porcentaje_bonificacion.max' => 'El porcentaje no puede ser mayor a 100',
            'periodos_bonificacion.required' => 'Los períodos de bonificación son requeridos',
            'periodos_bonificacion.integer' => 'Los períodos deben ser un número entero',
            'periodos_bonificacion.min' => 'Debe ser al menos 1 período',
            'periodos_bonificacion.max' => 'No puede ser más de 120 períodos (10 años)',
            'fecha_inicio.required' => 'La fecha de inicio es requerida',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar si ya existe una bonificación activa para este servicio
        $bonificacionExistente = BonificacionServicio::where('service_id', $request->service_id)
            ->vigentes($request->fecha_inicio)
            ->first();

        if ($bonificacionExistente) {
            return redirect()->back()
                ->withErrors(['service_id' => 'Ya existe una bonificación activa para este servicio en las fechas seleccionadas'])
                ->withInput();
        }

        BonificacionServicio::create([
            'service_id' => $request->service_id,
            'porcentaje_bonificacion' => $request->porcentaje_bonificacion,
            'periodos_bonificacion' => $request->periodos_bonificacion,
            'fecha_inicio' => $request->fecha_inicio,
            'activo' => true,
            'descripcion' => $request->descripcion
        ]);

        return redirect('/admin/bonificaciones')
            ->with(['status' => 'success', 'message' => 'Bonificación creada exitosamente.', 'icon' => 'fa-check']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bonificacion = BonificacionServicio::with('servicio')->find($id);
        
        if (!$bonificacion) {
            return back()->with(['status' => 'danger', 'message' => 'Bonificación no encontrada.', 'icon' => 'fa-frown-o']);
        }
        
        return View::make('admin.bonificaciones.show', compact('bonificacion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $bonificacion = BonificacionServicio::find($id);
        
        if (!$bonificacion) {
            return back()->with(['status' => 'danger', 'message' => 'Bonificación no encontrada.', 'icon' => 'fa-frown-o']);
        }
        
        $servicios = Servicio::all();
        return View::make('admin.bonificaciones.edit', compact('bonificacion', 'servicios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $bonificacion = BonificacionServicio::find($id);
        
        if (!$bonificacion) {
            return back()->with(['status' => 'danger', 'message' => 'Bonificación no encontrada.', 'icon' => 'fa-frown-o']);
        }
        
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:servicios,id',
            'porcentaje_bonificacion' => 'required|numeric|min:0|max:100',
            'periodos_bonificacion' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'activo' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with(['status' => 'danger', 'message' => 'Por favor corrige los errores.', 'icon' => 'fa-times']);
        }

        // Verificar si hay conflictos con otras bonificaciones
        $conflicto = BonificacionServicio::where('service_id', $request->service_id)
            ->where('id', '!=', $bonificacion->id)
            ->vigentes($request->fecha_inicio)
            ->exists();

        if ($conflicto) {
            return back()->withInput()
                ->with(['status' => 'danger', 'message' => 'Ya existe una bonificación activa para este servicio en el período especificado.', 'icon' => 'fa-times']);
        }

        $bonificacion->update([
            'service_id' => $request->service_id,
            'porcentaje_bonificacion' => $request->porcentaje_bonificacion,
            'periodos_bonificacion' => $request->periodos_bonificacion,
            'fecha_inicio' => $request->fecha_inicio,
            'activo' => $request->has('activo')
        ]);

        return redirect('/admin/bonificaciones')
            ->with(['status' => 'success', 'message' => 'Bonificación actualizada exitosamente.', 'icon' => 'fa-check']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $bonificacion = BonificacionServicio::find($id);
        
        if (!$bonificacion) {
            return back()->with(['status' => 'danger', 'message' => 'Bonificación no encontrada.', 'icon' => 'fa-frown-o']);
        }
        
        $bonificacion->delete();
        
        return redirect('/admin/bonificaciones')
            ->with(['status' => 'success', 'message' => 'Bonificación eliminada exitosamente.', 'icon' => 'fa-check']);
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $bonificacion = BonificacionServicio::find($id);
        
        if (!$bonificacion) {
            return back()->with(['status' => 'danger', 'message' => 'Bonificación no encontrada.', 'icon' => 'fa-frown-o']);
        }
        
        $bonificacion->update(['activo' => !$bonificacion->activo]);
        
        $status = $bonificacion->activo ? 'activada' : 'desactivada';
        
        return redirect()->back()
            ->with(['status' => 'success', 'message' => "Bonificación {$status} exitosamente.", 'icon' => 'fa-check']);
    }
}
