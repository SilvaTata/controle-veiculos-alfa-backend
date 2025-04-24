@extends('adminlte::page')
@section('css')
<link rel="stylesheet" href="{{ asset('css/custom-dark-mode.css') }}">

@endsection

@section('content_top_nav_right')
<li class="nav-item">
    <a id="dark-mode-toggle" class="nav-link" href="#" role="button">
        <i class="fas fa-moon"></i>
    </a>
</li>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const body = document.body;
        const darkMode = localStorage.getItem('darkMode');
        if (darkMode === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
    });
        
</script>
@endsection