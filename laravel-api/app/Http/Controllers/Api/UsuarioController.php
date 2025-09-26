<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Usuario::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Solo admin puede crear usuarios
        if (Auth::user()?->rol !== 'admin') {
            return response()->json([
                'message' => 'No autorizado: solo admin puede crear usuarios.'
            ], 403);
        }
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|string',
        ]);

        /**
         * Validar que el rol sea 'admin' o 'usuario'
         */
        if (!in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".',
                'status' => false
            ], 400);
        }


        $validated['password'] = Hash::make($validated['password']);

        $usuario = Usuario::create($validated);
        if (!$usuario) {
            return response()->json([
                'message' => 'Error al crear el usuario',
                'status' => false
            ], 500);
        }
        return response()->json([
            'message' => 'Usuario creado correctamente',
            'status' => true
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $usuario = Usuario::find($id);
        if(!$usuario){
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => false
            ], 404);
        }
        return response()->json($usuario);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Solo admin puede actualizar usuarios
        if (Auth::user()?->rol !== 'admin') {
            return response()->json([
                'message' => 'No autorizado: solo admin puede actualizar usuarios.'
            ], 403);
        }
        $usuario = Usuario::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|max:150|unique:usuarios,email,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'rol' => 'sometimes|required|string',
        ]);

        // Mensaje claro si mandan un rol inválido
        if (isset($validated['rol']) && !in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".'
            ], 422);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // no sobrescribir con null
        }

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Solo admin puede eliminar
        if (Auth::user()?->rol !== 'admin') {
            return response()->json([
                'message' => 'No autorizado: solo admin puede eliminar usuarios.'
            ], 403);
        }
        $usuario = Usuario::find($id);
        if(!$usuario){
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => false
            ], 404);
        }
        // Evitar borrar otro admin (opcional: permitir si se desea)
        if($usuario->rol === 'admin'){
            return response()->json([
                'message' => 'No se puede eliminar otro usuario admin.'
            ], 403);
        }
        $usuario->delete();
        return response()->json([
            'message' => 'Usuario eliminado correctamente',
            'status' => true
        ]);
    }
}
