@extends('layouts.user')
@section('content')
    <event-list
        :data="{{ json_encode([
            'events' => $events,
            'flashPage' => session()->has('Message.flashPage') ? session()->get('Message.flashPage')[0] : null,
            'urlCreate' => route('event.create'),
            'urlClose' => route('event.close'),
        ]) }}">
    </event-list>
    @php
        session()->forget('Message.flashPage');
    @endphp
@endsection