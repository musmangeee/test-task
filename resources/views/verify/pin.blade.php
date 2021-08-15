@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                @if(count($errors) > 0)
                    <div class="p-1">
                        @foreach($errors->all() as $error)
                            <div class="alert alert-warning alert-danger fade show" role="alert">{{$error}} <button type="button" class="close"
                                                                                                                    data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button></div>
                        @endforeach
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">{{ __('Verify Pin') }}</div>

                    <div class="card-body">


                        {{ __('Before proceeding, please check your email for a 6 Digit pin code.') }}

                        <form method="POST" action="{{ route('verify.pin') }}">
                            <label for="Enter Pin:"></label>
                            <input type="text" class="form-control" name="pin" pattern="[0-9]{6}" maxlength="6" required>

                            @csrf
                            <button type="submit" class="btn btn-primary mt-2">{{ __('Verify') }}</button>.
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
