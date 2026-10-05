@extends('layouts.admin')
@section('title', 'My Profile')
@section('content')
@include('partials.profile-content', ['routePrefix' => 'admin'])
@endsection
