@extends('pdf.layouts.dress-document', ['title' => 'Scheda Produzione Abito #' . $dress->id])

@section('content')
    @include('pdf.partials.dress-model-production-page', ['dress' => $dress, 'document' => $document])
@endsection
