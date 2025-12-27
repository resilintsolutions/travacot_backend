@extends('layouts.app')
@section('content')
  <h1>Inventory — Hotels</h1>
  <input id="inv_query" placeholder="search hotels">
  <button id="inv_search">Search</button>
  <div id="inv_list"></div>
@endsection
@push('scripts')
<script type="module" src="@vite('resources/js/admin.js')"></script>
@endpush
