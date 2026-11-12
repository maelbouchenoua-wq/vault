<?php

function respond_success(?string $message): void
{
    echo json_encode(['message' => $message]);
}

function respond_success_with_content(array $content): void
{
    # JSON_UNESCAPED_SLASHES Is used not to break HTML content. https://stackoverflow.com/a/5064590
    echo json_encode($content, JSON_UNESCAPED_SLASHES);
}


function respond_error(?int $status = 500, ?string $message = "Une erreur est survenue"): void
{
    http_response_code($status);
    echo json_encode(['message' => $message]);
}