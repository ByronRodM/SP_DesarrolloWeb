<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
// Excel fallback deshabilitado por falta de extension gd; usando CSV streaming

class TareaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tareas = Tarea::with('usuario:id,nombre')->get()->map(function($t){
            return [
                'id' => $t->id,
                'titulo' => $t->titulo,
                'descripcion' => $t->descripcion,
                'estado' => $t->estado,
                'fecha_vencimiento' => $t->fecha_vencimiento,
                'usuario' => $t->usuario?->nombre,
                'usuario_id' => $t->usuario_id,
                'created_at' => $t->created_at,
                'updated_at' => $t->updated_at,
            ];
        });
        return response()->json($tareas);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Solo admin
        if(Auth::user()?->rol !== 'admin'){
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $validated = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'titulo' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'estado' => 'nullable|in:pendiente,en_progreso,completada',
            'fecha_vencimiento' => 'nullable|date'
        ]);

        if(!isset($validated['estado'])){
            $validated['estado'] = 'pendiente';
        }

        $tarea = Tarea::create($validated);

        return response()->json([
            'message' => 'Tarea creada correctamente',
            'data' => $tarea
        ], 201);
    }

    /**
     * Update partial fields (estado, titulo, descripcion, fecha_vencimiento, usuario_id)
     */
    public function update(Request $request, $id)
    {
        if(Auth::user()?->rol !== 'admin'){
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $tarea = Tarea::find($id);
        if(!$tarea){
            return response()->json(['message' => 'Tarea no encontrada'], 404);
        }
        $validated = $request->validate([
            'usuario_id' => 'sometimes|exists:usuarios,id',
            'titulo' => 'sometimes|string|max:150',
            'descripcion' => 'sometimes|nullable|string',
            'estado' => 'sometimes|in:pendiente,en_progreso,completada',
            'fecha_vencimiento' => 'sometimes|nullable|date'
        ]);
        $tarea->update($validated);
        return response()->json(['message' => 'Tarea actualizada', 'data' => $tarea]);
    }

    /**
     * Export pending tasks to Excel (streamed XLSX)
     */
    public function exportPending()
    {
        if(Auth::user()?->rol !== 'admin'){
            abort(403, 'No autorizado');
        }
        $fileName = 'tareas_pendientes_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\""
        ];
        return response()->stream(function(){
            $out = fopen('php://output','w');
            fwrite($out, chr(239).chr(187).chr(191));
            fputcsv($out, ['ID','Titulo','Descripcion','Estado','Usuario','Fecha Vencimiento','Creado']);
            Tarea::with('usuario:id,nombre')
                ->where('estado','pendiente')
                ->orderBy('fecha_vencimiento')
                ->chunk(200, function($chunk) use ($out){
                    foreach($chunk as $t){
                        fputcsv($out,[
                            $t->id,
                            $t->titulo,
                            $t->descripcion,
                            $t->estado,
                            $t->usuario?->nombre,
                            $t->fecha_vencimiento,
                            $t->created_at
                        ]);
                    }
                });
            fclose($out);
        }, 200, $headers);
    }
}
