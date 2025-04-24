<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="{{ asset('css/custom-register.css') }}">
        <title>Registrar</title>
    </head>
    <body>
        <h1 class="Registrar"></h1>
        
        <form method="POST" action="{{ route('register') }}" class="container">
            @csrf
            <h2>Registrar</h2>
            <section class="input-box">
                <input type="text" name="name" placeholder="Digite seu nome" value="{{ old('name') }}" required autofocus />
                <i class="bx bxs-user"></i>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </section>
            
            <section class="input-box">
                <input type="text" name="cpf" placeholder="Digite o CPF" value="{{ old('cpf') }}" required />
                <i class="bx bxs-id-card"></i>
                @error('cpf')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </section>

            <section class="input-box">
                <input type="text" name="telefone" placeholder="Digite o Telefone" value="{{ old('telefone') }}" required />
                <i class="bx bxs-id-card"></i>
                @error('telefone')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </section>
            
            <section class="input-box">
                <input type="email" name="email" placeholder="Digite seu e-mail" value="{{ old('email') }}" required />
                <i class="bx bxs-envelope"></i>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </section>
            
            <section class="custom-select-wrapper">
                <select name="cargo" class="custom-select" required>
                    <option value="" disabled selected>Escolha um centro de custo</option>
                    <option value="0">Adm</option>
                    <option value="1">User</option>
                </select>
                <i class="bx bxs-briefcase"></i>
                @error('cargo')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </section>
            
            <section class="input-box">
                <input type="password" name="password" placeholder="Digite sua senha" required />
                <i class="bx bxs-lock-alt"></i>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </section>
            
            <section class="input-box">
                <input type="password" name="password_confirmation" placeholder="Confirme sua senha" required />
                <i class="bx bxs-lock"></i>
            </section>
            
            <section class="button-container">
                <button class="login-button" type="submit">Registrar</button>
            </section>
        </form>

        <script>
            // Espera até o conteúdo carregar
            document.addEventListener('DOMContentLoaded', function () {
                // Seleciona todas as mensagens de erro
                const errorMessages = document.querySelectorAll('.invalid-feedback');

                errorMessages.forEach(function (message) {
                    // Define um timeout para esconder a mensagem após 5 segundos
                    setTimeout(function () {
                        message.style.display = 'none';
                    }, 5000); // 5000ms = 5 segundos
                });
            });
        </script>
    </body>
</html>