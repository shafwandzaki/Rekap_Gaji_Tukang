@extends('app')

@section('isi')

    <h1 class="mb-6 text-2xl font-bold">Edit Rekap Gaji</h1>

    <form method="POST" action="{{ route('rekap.update', $rekap) }}">
        @csrf
        @method('PUT')
        @include('rekap.form')
    </form>

@endsection