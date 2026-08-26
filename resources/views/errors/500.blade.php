@include('errors.any', [
    'status' => 500,
    'error' => 'Internal Server Error',
    'message' => isset($exception) ? $exception->getMessage() : 'Terjadi kesalahan internal pada server.'
])
