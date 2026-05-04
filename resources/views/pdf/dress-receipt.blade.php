@extends('pdf.layouts.dress-document', ['title' => 'Modellino Abito #' . $dress->id])

@section('content')
    @include('pdf.partials.dress-model-overview-page', ['dress' => $dress, 'document' => $document])

    <div class="page-break"></div>
    @include('pdf.partials.dress-model-production-page', ['dress' => $dress, 'document' => $document])

    <div class="page-break"></div>
    @include('pdf.partials.dress-model-technical-page', ['dress' => $dress, 'document' => $document])
@endsection
