<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; 

class AuthController extends Controller
{
    public function users()
    {
        return response()->json(User::with(['cargo'])->get(), 200);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'cpf' => 'required|string|unique:users,cpf',
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'senha' => 'required|string|min:6',
            'cargo' => 'required|string|in:Adm,User',
            'status' => 'required|string|in:ativo,inativo',
            'telefone' => 'required|string|min:10|max:15', 
        ]);
    
        $cargo = Cargo::where('nome', $data['cargo'])->first();
        if (!$cargo) {
            return response()->json(['error' => 'Cargo inválido'], 400);
        }
    
        DB::beginTransaction();
        try {
            $user = User::create([
                'cpf' => $data['cpf'],
                'name' => $data['name'],
                'password' => Hash::make($data['senha']),
                'cargo_id' => $cargo->id,
                'status' => $data['status'],
                'telefone' => $data['telefone'],
                'email' => $data['email']
            ]);

            DB::commit();
    
            $token = $user->createToken('auth_token')->plainTextToken;
    
            return response()->json([
                'message' => 'Usuário cadastrado com sucesso!',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'error' => 'Erro ao registrar usuário.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'cpf' => 'required|string',
            'password' => 'required|string',
            'cargo_id' => [
                'required',         
                'integer',         
                Rule::in([1, 2]), 
            ],
        ]);

        $user = User::where('cpf', $request->cpf)->first();

        if (!$user || !Hash::check($request->password, $user->password)  || $user->cargo_id !== $request->cargo_id ) {
            throw ValidationException::withMessages([
                'cpf' => ['As credenciais (CPF, Senha ou Cargo) estão incorretas.'],
            ]);
        }

        if ($user->status !== 'ativo') { 
             throw ValidationException::withMessages([
                'cpf' => ['Este usuário está inativo.'],
             ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso!',
            'token' => $token,
            'user' => $user->load(['cargo']),
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso'], 200);
    }    
}
