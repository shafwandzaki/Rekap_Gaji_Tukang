@extends('app')

@section('isi')

    <h1 class="mb-6 font-inter text-2xl font-bold">Edit Pekerja</h1>

    <form method="POST" action="{{ route('pekerja.update', $pekerja) }}">
        @csrf
        @method('PUT')
        @include('pekerja.form')
    </form>

@endsection