<?php

namespace App\Http\Middleware;

use App\Services\ExerciseTranslationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetRequestLocale
{
    public function __construct(private ExerciseTranslationService $translations)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Only honor explicit client locale. Do NOT use Accept-Language here:
        // mobile runtimes often send en-US and would override Arabic exercise content.
        $explicit = $request->header('X-Locale') ?: $request->query('locale');

        $resolved = $this->translations->resolveLocale(
            $explicit ? (string) $explicit : null
        );

        App::setLocale($resolved);
        config(['exercise_localization.runtime_locale' => $resolved]);

        return $next($request);
    }
}
