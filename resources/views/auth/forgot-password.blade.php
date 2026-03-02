@extends('layouts.auth')

@section('title', 'Mot de passe oublié')
@section('auth-title', 'Mot de passe oublié ?')
@section('auth-subtitle', 'Entrez votre email pour réinitialiser')

@section('content')
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    
    {{-- Instructions --}}
    <div class="alert alert-light mb-4">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        Entrez l'adresse email associée à votre compte. Nous vous enverrons un lien pour réinitialiser votre mot de passe.
    </div>
    
    {{-- Email --}}
    <div class="form-floating mb-4">
        <input 
            type="email" 
            class="form-control @error('email') is-invalid @enderror" 
            id="email" 
            name="email" 
            placeholder="nom@exemple.com"
            value="{{ old('email') }}"
            required 
            autofocus
        >
        <label for="email"><i class="fas fa-envelope me-2"></i>Adresse email</label>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- Submit Button --}}
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-paper-plane me-2"></i>Envoyer le lien de réinitialisation
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="mb-0">
    <a href="{{ route('login') }}">
        <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
    </a>
</p>
@endsection
