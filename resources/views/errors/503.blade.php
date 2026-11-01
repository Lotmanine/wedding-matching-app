@extends('errors.layout')
@section('code', '503')
@section('title', __('Under maintenance'))
@section('message', __(':brand is temporarily unavailable. Please try again in a few minutes.', ['brand' => __('form.brand')]))
