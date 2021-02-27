@extends('layouts.app')

@section('content')
<section class="container mt-5">
    <form>
        <div class="mb-3">
            <label for="exampleFormControlInput1" class="form-label">Amount</label>
            <input type="number" class="form-control" id="exampleFormControlInput1" placeholder="Amount">
        </div>
        <div class="mb-3">
            <label for="exampleFormControlTextarea1" class="form-label">Description</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</section>
@endsection