@extends('app')

@section('isi')

    <h1 class="mb-6 font-inter text-2xl font-bold">Tambah Pekerja</h1>

    <form method="POST" action="{{ route('pekerja.store') }}">
        @csrf
        @include('pekerja.form')
    </form>

@endsection