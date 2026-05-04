@extends('pdf.layouts.dress-document', ['title' => 'Scheda Tecnica Abito #' . $dress->id])

@section('content')
    @include('pdf.partials.dress-model-technical-page', ['dress' => $dress, 'document' => $document])
@endsection
