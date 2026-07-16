@if (session('flash'))
  @php $f = session('flash'); @endphp
  <div class="alert alert-{{ ($f['type'] ?? '') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <i class="bi {{ ($f['type'] ?? '') === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
    {{ $f['text'] ?? '' }}
  </div>
@endif
