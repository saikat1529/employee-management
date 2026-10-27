@extends('docs.layout', ['title' => 'Documents'])

@section('content')
    @if (! ($configured ?? true))
        <h1>Documents are not configured</h1>
        <p>The document password has not been set on this server, so the documents cannot be opened. Ask the site owner to set DOCS_PASSWORD.</p>
    @else
        <h1>Documents</h1>
        <p>Enter the document password to continue.</p>

        <form method="POST" action="{{ route('docs.login.submit') }}">
            @csrf
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required autofocus>

            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit">Open documents</button>
        </form>
    @endif
@endsection
