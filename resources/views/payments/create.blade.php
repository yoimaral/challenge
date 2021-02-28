@extends('layouts.app')

@section('content')
<section class="container">
    <div class="alert alert-primary d-flex justify-content-between" role="alert">
        <p class="my-auto">Pagos PlacetoPay</p>
        <a href="{{route('payments.index')}}" class="btn btn-success ml-2">Ver todos los pagos</a>
    </div>
    <form action="{{route('payments.store')}}" method="POST">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="inputState">Medio de pago</label>
                <select class="custom-select @error('payment_method_id') is-invalid @enderror" name="payment_method_id">
                    <option value="0">Por favor selecione el medio de pago</option>
                    @foreach ($paymentMethods as $paymentMethod)
                    <option value="{{$paymentMethod->id}}"
                        {{old('payment_method_id') == $paymentMethod->id ? 'selected' : ''}}>
                        {{$paymentMethod->name}}
                    </option>
                    @endforeach
                </select>
                @error('payment_method_id')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group col-md-6">
                <label for="inputNumber4">Monto a pagar</label>
                <input name="amount" type="number" class="form-control @error('amount') is-invalid @enderror"
                    id="inputNumber4" value="{{old('amount')}}">
                @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="form-group">
            <label for="exampleFormControlTextarea1"
                class="form-label @error('description') is-invalid @enderror">Descripción del pago</label>
            <textarea name="description" class="form-control" id="exampleFormControlTextarea1"
                rows="3">{{old('description', 'Pago de prueba')}}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Pagar</button>
    </form>
</section>
@endsection