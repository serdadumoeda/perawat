<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API v1 Docs - Platform Keperawatan</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
        :root {
            color-scheme: light;
        }
        body {
            margin: 0;
            background: #f4f9f8;
            font-family: "Segoe UI", Roboto, sans-serif;
            color: #0f172a;
        }
        .docs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #d1e3df;
            background: linear-gradient(120deg, #0f766e, #0d9488);
            color: #fff;
        }
        .docs-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
        }
        .docs-link {
            color: #d1fae5;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
        }
        #swagger-ui {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 1rem 2rem;
        }
    </style>
</head>
<body>
    <header class="docs-header">
        <h1 class="docs-title">Platform Keperawatan API v1</h1>
        <a class="docs-link" href="{{ route('api.v1.openapi') }}" target="_blank" rel="noopener">OpenAPI JSON</a>
    </header>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.onload = function () {
            SwaggerUIBundle({
                url: @json($openApiEndpoint),
                dom_id: '#swagger-ui',
                deepLinking: true,
                displayRequestDuration: true,
                tryItOutEnabled: true,
                presets: [SwaggerUIBundle.presets.apis],
            });
        };
    </script>
</body>
</html>
