@include('errors.any', [
    'status' => 404,
    'error' => 'Not Found',
    'message' => isset($exception) ? $exception->getMessage() : 'Halaman tidak ditemukan.'
])
