@extends('errors.layout')
@section('code', '429')
@section('title', __('Too many requests'))
@section('message', __('You made too many requests in a short time. Please wait a moment before trying again.'))
