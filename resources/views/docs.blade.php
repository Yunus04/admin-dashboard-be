<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Admin Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />
    <style>
        /* Custom Swagger UI Enhancements */
        .swagger-ui .topbar {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 10px 20px;
            display: flex;
            align-items: center;
        }

        .swagger-ui .topbar .topbar-wrapper {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .swagger-ui .topbar .topbar-wrapper img {
            height: 30px;
            margin-right: 15px;
        }

        .swagger-ui .topbar .topbar-wrapper span {
            color: #fff;
            font-size: 18px;
            font-weight: 600;
        }

        /* Custom dropdown styling for parameter inputs */
        .swagger-ui .parameters-col_description select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            background-color: #fafafa;
            font-size: 14px;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .swagger-ui .parameters-col_description select:hover {
            border-color: #5476d6;
        }

        .swagger-ui .parameters-col_description select:focus {
            outline: none;
            border-color: #5476d6;
            box-shadow: 0 0 0 2px rgba(84, 118, 214, 0.2);
        }

        /* Search input styling */
        .swagger-ui .parameters-col_description input[type="text"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            background-color: #fafafa;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .swagger-ui .parameters-col_description input[type="text"]:focus {
            outline: none;
            border-color: #5476d6;
            box-shadow: 0 0 0 2px rgba(84, 118, 214, 0.2);
            background-color: #fff;
        }

        /* Checkbox styling for sort order */
        .swagger-ui .parameters-col_description input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #5476d6;
        }

        /* Enhance info section */
        .swagger-ui .info {
            margin: 30px 0;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 8px;
            border-left: 4px solid #5476d6;
        }

        .swagger-ui .info .title {
            color: #1a1a2e;
            font-size: 24px;
            font-weight: 700;
        }

        .swagger-ui .info .description p {
            color: #495057;
            line-height: 1.6;
        }

        /* Card styling for endpoints */
        .swagger-ui .operation {
            margin: 15px 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e9ecef;
        }

        .swagger-ui .operation:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Method badge colors */
        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background: #61affe;
        }

        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background: #49cc90;
        }

        .swagger-ui .opblock.opblock-put .opblock-summary-method {
            background: #fca130;
        }

        .swagger-ui .opblock.opblock-delete .opblock-summary-method {
            background: #f93e3e;
        }

        /* Response status colors */
        .swagger-ui .response-col_status {
            font-weight: 600;
        }

        .response-200 .response-col_status,
        .response-201 .response-col_status {
            color: #49cc90;
        }

        .response-400 .response-col_status,
        .response-401 .response-col_status,
        .response-403 .response-col_status,
        .response-404 .response-col_status,
        .response-422 .response-col_status {
            color: #f93e3e;
        }

        /* Schema styling */
        .swagger-ui .model-box {
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .swagger-ui .model-title {
            color: #1a1a2e;
            font-weight: 600;
        }

        /* Tag styling */
        .swagger-ui .tag {
            border-bottom: 2px solid #5476d6;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .swagger-ui .tag:hover {
            color: #5476d6;
        }

        /* Loading animation */
        .swagger-ui .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }

        .swagger-ui .loading-container::after {
            content: "Loading API Documentation...";
            color: #5476d6;
            font-size: 16px;
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }

        /* Dark mode support toggle */
        .theme-toggle {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .theme-toggle:hover {
            background: #f8f9fa;
        }

        /* Pagination info styling */
        .param-example {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <span id="theme-icon">🌙</span>
        <span id="theme-text">Dark</span>
    </button>

    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-standalone-preset.js"></script>
    <script>
        let isDarkMode = false;

        function toggleTheme() {
            isDarkMode = !isDarkMode;
            document.body.classList.toggle('swagger-ui--dark');

            const icon = document.getElementById('theme-icon');
            const text = document.getElementById('theme-text');

            if (isDarkMode) {
                icon.textContent = '☀️';
                text.textContent = 'Light';
                document.documentElement.style.filter = 'invert(1) hue-rotate(180deg)';
            } else {
                icon.textContent = '🌙';
                text.textContent = 'Dark';
                document.documentElement.style.filter = '';
            }
        }

        window.onload = function() {
            // Wait for Swagger UI to initialize
            setTimeout(function() {
                // Enhance dropdown inputs with better styling
                const selects = document.querySelectorAll('.parameters-col_description select');
                selects.forEach(select => {
                    select.setAttribute('title', 'Select an option from the dropdown');
                });

                // Add placeholder to search inputs
                const textInputs = document.querySelectorAll('.parameters-col_description input[type="text"]');
                textInputs.forEach(input => {
                    if (input.name === 'search' || input.name === 'filter') {
                        input.setAttribute('placeholder', 'Type to search...');
                    }
                });

                // Add checkbox labels
                const checkboxes = document.querySelectorAll('.parameters-col_description input[type="checkbox"]');
                checkboxes.forEach(checkbox => {
                    const label = checkbox.closest('.parameters-col');
                    if (label) {
                        label.style.display = 'flex';
                        label.style.alignItems = 'center';
                        label.style.gap = '8px';
                    }
                });
            }, 1000);

            SwaggerUIBundle({
                url: '/api/docs/json',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                layout: 'StandaloneLayout',
                showCommonExtensions: true,
                showRequestDuration: true,
                filter: true,
                docExpansion: 'none',
                defaultModelsExpandDepth: 3,
                defaultModelExpandDepth: 3,
                displayRequestDuration: true,
                tryItOutEnabled: true,
                persistAuthorization: true,
                onComplete: function() {
                    console.log('Swagger UI initialized successfully');
                }
            });
        };
    </script>
</body>
</html>
