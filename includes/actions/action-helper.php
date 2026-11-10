<?php

function respond_success(?string $message): void
{
    echo json_encode(['message' => $message]);
}


function respond_error(?int $status = 500, ?string $message = "Une erreur est survenue"): void
{
    http_response_code($status);
    echo json_encode(['message' => $message]);
}