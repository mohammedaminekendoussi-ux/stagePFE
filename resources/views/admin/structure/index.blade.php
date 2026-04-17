@extends('layouts.admin')

@section('title', 'Structure Académique')
@section('page-title', 'Gestion de la Structure Académique')

@section('content')

    {{-- Message succès --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Menu onglets --}}
    <div class="card mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill" style="border-bottom: none;">
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $tab === 'filieres' ? 'active fw-bold' : '' }}"
                       href="{{ route('admin.structure.filieres') }}">
                        <i class="bi bi-diagram-3 me-2"></i>Filières
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $tab === 'groupes' ? 'active fw-bold' : '' }}"
                       href="{{ route('admin.structure.groupes') }}">
                        <i class="bi bi-people me-2"></i>Groupes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-3 {{ $tab === 'modules' ? 'active fw-bold' : '' }}"
                       href="{{ route('admin.structure.modules') }}">
                        <i class="bi bi-book me-2"></i>Modules
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Contenu selon onglet actif --}}
    @if($tab === 'filieres')
        @include('admin.structure.filieres')
    @elseif($tab === 'groupes')
        @include('admin.structure.groupes')
    @elseif($tab === 'modules')
        @include('admin.structure.modules')
    @endif

@endsection