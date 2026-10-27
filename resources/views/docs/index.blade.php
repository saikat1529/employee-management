@extends('docs.layout', ['title' => 'Documents'])

@section('content')
    <h1>Documents</h1>
    <p>Choose a document to open.</p>

    <ul class="docs">
        @foreach ($documents as $slug => $label)
            <li><a href="{{ route('docs.show', $slug) }}">{{ $label }}</a></li>
        @endforeach
    </ul>

    <form class="inline" method="POST" action="{{ route('docs.logout') }}">
        @csrf
        <button type="submit" class="secondary">Log out</button>
    </form>
@endsection
