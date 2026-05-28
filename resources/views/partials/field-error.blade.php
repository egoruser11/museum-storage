@if ($errors->has($name))
    <div class="field-error">{{ $errors->first($name) }}</div>
@endif
