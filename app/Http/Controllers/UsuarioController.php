<?php

namespace App\Http\Controllers;

    use App\Models\User;
    use App\Models\Cargo;
    use Illuminate\Http\Request;
    use Illuminate\Validation\Rule;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\Hash;

    class UsuarioController extends Controller
    {
        public function index(Request $request)
    {
        $query = User::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orwhere('email', 'like', '%' . $request->search . '%')
                  ->orwhere('cpf', 'like', '%' . $request->search . '%');
                    
        }
        $users = $query->paginate(1000);
    
        return view('teste.index', compact('users'));
    
    }
        public function create()
        {
            return view('teste.create');
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'cpf' => ['required', 'string', 'size:11', 'unique:users,cpf'], 
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], 
                'telefone' => ['required', 'string', 'max:20'], 
                'cargo' => [ 
                    'required',
                    Rule::in(['1', '2']), 
                ],
                'password' => ['required', 'string', 'min:8', 'confirmed'], 
            ]);

            if ($validator->fails()) {
                return redirect()->route('teste.create') 
                            ->withErrors($validator)
                            ->withInput(); 
            }

            try {
                $cargoId = $request->input('cargo'); 

                User::create([
                    'name' => $request->input('name'),
                    'cpf' => $request->input('cpf'),
                    'email' => $request->input('email'),
                    'telefone' => $request->input('telefone'),
                    'cargo_id' => $cargoId, 
                    'password' => Hash::make($request->input('password')), 
                    'status' => 'ativo', 
                ]);

                return redirect()->route('teste.index')->with('sucess', 'Usuário criado com sucesso!'); 

            } catch (\Exception $e) {
                return redirect()->route('teste.create')
                            ->with('error', 'Erro ao criar usuário. Tente novamente.') 
                            ->withInput();
            }
        }

        public function show($id)
        {
            $user = User::findOrFail($id);
            return view('teste.show', compact('user'));
        }

        public function edit($id)
        {
            $user = User::findOrFail($id);
            

            return view('teste.edit', compact('user'));
        }

        public function update(Request $request, $id)
        {
            $user = User::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id), 
                ],
                'cpf' => [
                    'required',
                    'string',
                    'size:11',
                    Rule::unique('users')->ignore($user->id), 
                ],
                'cargo' => 'required|in:1,2', 
                'telefone' => 'required|string|max:20', 
            ]);

            $updateData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'cpf' => $validatedData['cpf'], 
                'telefone' => $validatedData['telefone'], 
                'cargo_id' => $validatedData['cargo'],
            ];

            try {
                $user->update($updateData);

                return redirect()->route('teste.index')->with('sucess', 'Usuário atualizado com sucesso!');

            } catch (\Exception $e) {
                return redirect()->back()
                            ->with('error', 'Erro ao atualizar o usuário. Tente novamente.')
                            ->withInput(); // Mantém os dados digitados no formulário
            }
        }

        public function destroy($id)
        {
            $user = User::findOrFail($id);

            $user->delete();
            return redirect()->route('teste.index')->with('success', 'Usuário excluído com sucesso!');
        }

        public function permissao(Request $request, User $user, $id) {

            $user = User::findOrFail($id);
            return view('teste.permissao', compact('user'));
            
        }
 
        public function mudarStatusU(Request $request, $id)
        {
            try {
                $user = User::findOrFail($id);

                $request->validate([
                    'status' => 'required|in:ativo,inativo',
                ]);

                $user->update([
                    'status' => $request->status,
                ]);

                return response()->json([
                    'success' => true,
                    'status' => $user->status,
                    'message' => 'Status atualizado com sucesso',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar status: ' . $e->getMessage(),
                ], 500);
            }
        }

        

    }
