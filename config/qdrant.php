<?php

return [
    'url' => env('QDRANT_URL', 'http://localhost:6333'),
    'api_key' => env('QDRANT_API_KEY', null),
    'timeout' => env('QDRANT_TIMEOUT', 30),
    'collections' => [
        'document_chunks' => [
            'vector_size' => 3072,
            'distance' => 'Cosine',
        ]
    ]
];
