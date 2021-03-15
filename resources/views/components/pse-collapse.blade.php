<label for="inputState">Has seleccionado PSE como medio de pago</label>
<select class="custom-select @error('bank_code') is-invalid @enderror" name="bank_code">
    @foreach ($bankList as $bank)
    <option value="{{$bank['code']}}" {{old('bank_code') == $bank['code'] ? 'selected' : ''}}>
        {{$bank['name']}}
    </option>
    @endforeach
</select>
@error('bank_code')
<div class="invalid-feedback">Debes seleccionar un banco de la lista</div>
@enderror