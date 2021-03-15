@extends('layouts.app')

@section('content')
<section class="container">
    <div class="alert alert-primary d-flex justify-content-between" role="alert">
        <p class="my-auto">Pagos PlacetoPay</p>
        <a href="{{route('payments.index')}}" class="btn btn-success ml-2">Ver todos los pagos</a>
    </div>
    <form action="{{route('payments.store')}}" method="POST">
        @csrf
        <div class="form-group">
            <label for="inputNumber4">Monto a pagar</label>
            <input name="amount" type="number" class="form-control @error('amount') is-invalid @enderror"
                id="inputNumber4" value="{{old('amount')}}">
            @error('amount')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="exampleFormControlTextarea1"
                class="form-label @error('description') is-invalid @enderror">Descripción
                del pago</label>
            <textarea name="description" class="form-control" id="exampleFormControlTextarea1"
                rows="3">{{old('description', 'Pago de prueba')}}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="inputState">Medio de pago</label>
            <div class="form-group" id="toggler">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    @foreach ($paymentMethods as $paymentMethod)
                    <label class="btn btn-outline-secondary rounded m-2 p-1"
                        data-target="#{{ $paymentMethod->name }}Collapse" data-toggle="collapse">
                        <input type="radio" name="payment_method_id" value="{{ $paymentMethod->id }}" required>
                        <img style="width: 250px;" class="img-thumbnail h-100" src="{{ asset($paymentMethod->image) }}">
                    </label>
                    @endforeach
                </div>
                @foreach ($paymentMethods as $paymentMethod)
                <div id="{{ $paymentMethod->name }}Collapse" class="collapse" data-parent="#toggler">
                    @includeIf('components.' . strtolower($paymentMethod->name) . '-collapse')
                </div>
                @endforeach
            </div>
            @error('payment_method_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Pagar</button>
    </form>
</section>
@endsection