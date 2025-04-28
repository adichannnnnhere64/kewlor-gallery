<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <script>
            if (typeof(Storage) !== "undefined") {
                if(localStorage.getItem('dark_mode') && localStorage.getItem('dark_mode') == 'true'){
                    document.documentElement.classList.add('dark');
                }
            }
        </script>


        @vite(['resources/css/app.css', 'resources/js/app.js'])

   <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <link href="https://releases.transloadit.com/uppy/v4.13.3/uppy.min.css" rel="stylesheet" />
    <script src="https://releases.transloadit.com/uppy/v4.13.3/uppy.min.js"></script>


        <title>{{ $title ?? 'Kewlor' }}</title>
    </head>
    <body style="background-color: {{ setting('bgcolor') ?? '#fff' }};" class="min-h-screen antialiased dark:bg-gradient-to-b dark:from-gray-950 dark:to-gray-900">
        {{ $slot }}
        <livewire:toast />
    </body>
</html>
