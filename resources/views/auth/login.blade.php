<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="{{ asset('css/custom-login.css') }}">
        <title>Login</title>
    </head>
    @section('content_header')

    <script>
    setTimeout(function() {
        const errorMessages = document.querySelectorAll('.invalid-feedback');
        
        errorMessages.forEach(function(message) {
            message.style.display = 'none';
        });
    }, 4000); 
    </script>

        @if(session('success'))
            <div class="alert alert-success" id="message" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if(session('danger'))
            <div class="alert alert-danger" id="message" role="alert">
                {{ session('danger') }}
            </div>
        @endif
    @endsection
    <body>
    <h1 class="login-title"></h1>
        
        <form method="POST" action="{{ route('login') }}" class="container">
            @csrf
            <h2>Login</h2>
            <section class="input-box" request>
                <input type="text" name="cpf" placeholder="Digite o CPF" />
                <i class="bx bxs-user"></i>
                @error('cpf')
                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </section>
            
            <section class="input-box" request>
                <input type="password" name="password" placeholder="Digite a Senha" />
                <i class="bx bxs-lock-alt"></i>
                @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                @enderror
            </section>

            <button class="login-button" type="submit">Login</button>
            <br>
            <h5 class="dont-have-an-account">
                Não tem uma conta?
                <a href="{{ route('register') }}"><b>Registre-se</b></a>
            </h5>
            
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    const errorMessages = document.querySelectorAll('.invalid-feedback');
                    
                    errorMessages.forEach(function(message) {
                        message.style.display = 'none';
                    });
                }, 5000);
            });
        </script>
    </body>
    </html>
