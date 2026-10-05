@extends('layouts.teacher')
@section('title', 'My Profile')
@section('content')
@include('partials.profile-content', ['routePrefix' => 'teacher'])
@endsection
