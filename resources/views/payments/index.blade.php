@extends('layouts.app')
@section('content')
<section class="container d-flex flex-column align-items-center">
    <h1>Pagos PlacetoPay</h1>
    @if (!isset($payments) || $payments->isEmpty())
    <div class="alert alert-info w-25 mx-auto" role="alert">
        <p class="text-center mb-0">
            No hay pagos disponibles
        </p>
    </div>
    @else
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="text-center">Referencia</th>
                <th>Método de pago</th>
                <th>Monto original</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
            <tr>
                <td class="text-center align-middle">{{$payment->reference}}</td>
                <td class="align-middle">{{$payment->paymentMethod->name}}</td>
                <td class="align-middle">{{$payment->formattedAmount}}</td>
                <td class="text-center align-middle">
                    <span class="badge
                        @if($payment->status === \App\Models\Payment::STATUSES['APPROVED'])
                                badge-success
@endif
                            @if($payment->status === \App\Models\Payment::STATUSES['REJECTED'])
                                badge-danger
@endif
                            @if($payment->status === \App\Models\Payment::STATUSES['PENDING'])
                                badge-warning
@endif
                            @if($payment->status == \App\Models\Payment::STATUSES['IN PROCESS'])
                                badge-info
@endif
                                text-wrap mx-auto" style="font-size: 14px;">
                        <span class="display-5 text-center">{{$payment->status}}</span>
                    </span>
                </td>
                <td class="text-center align-middle">
                    <a href="{{route('payments.show', $payment)}}" class="btn btn-outline-dark">Ver</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    <div class="w-25 mx-auto mb-3">
        <a href="{{route('payments.create')}}" class="link-bold btn btn-outline-danger w-100">Volver</a>
    </div>
</section>
@endsection