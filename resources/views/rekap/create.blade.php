@extends('app')

@section('isi')

    <h1 class="mb-6 text-2xl font-bold">Rekap Gaji</h1>

    <form method="POST" action="{{ route('rekap.store') }}">
        @csrf
        @include('rekap.form')
    </form>

@endsection