@extends('layouts.app')
@section('content')
<section class="container">
    <div class="my-4">
        <div class="jumbotron mb-0 py-3">
            <div class="alert
                        @if($payment->status === \App\Models\Payment::STATUSES['APPROVED'])
                        alert-success
@endif
                    @if($payment->status === \App\Models\Payment::STATUSES['REJECTED'])
                        alert-danger
@endif
                    @if($payment->status === \App\Models\Payment::STATUSES['PENDING'])
                        alert-warning
@endif
                    @if($payment->status == \App\Models\Payment::STATUSES['IN PROCESS'])
                        alert-info
@endif
                        mx-auto" role="alert">
                <h1 class="display-5 text-center">{{$payment->status}}</h1>
            </div>
            <hr class="my-4">
            <div class="d-flex flex-column align-items-center">
                <p><span class="font-weight-bold">Fecha</span>: {{$payment->updated_at}}</p>
                <p><span class="font-weight-bold">Monto original</span>:
                    {{$payment->formattedAmount}}</p>
                <p><span class="font-weight-bold">Referencia</span>: {{$payment->reference}}</p>
                <p><span class="font-weight-bold">Estado</span>: {{$payment->status}}</p>
                <p><span class="font-weight-bold">Medio de pago</span>: {{$payment->paymentMethod->name}}
                </p>
            </div>
            <div class="d-flex justify-content-center">
                @if($payment->status === \App\Models\Payment::STATUSES['IN PROCESS'])
                <form class="d-flex justify-content-center" action="{{route('payments.retry', $payment)}}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <button class="btn btn-success" type="submit">Reintentar pago</button>
                </form>
                @endif
                <a href="{{route('payments.index')}}" class="btn btn-danger ml-2">Volver</a>
            </div>
        </div>
    </div>
</section>
@endsection