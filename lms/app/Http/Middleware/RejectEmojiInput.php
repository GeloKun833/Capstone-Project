<?php

namespace App\Http\Middleware;

use App\Support\NoEmoji;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectEmojiInput
{
    /** Fields that are never checked (system / binary). */
    private const SKIP_KEYS = [
        '_token',
        '_method',
        '_previous',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $payload = $request->except(array_merge(self::SKIP_KEYS, array_keys($request->allFiles())));
        $fields = NoEmoji::findFields($payload);

        if ($fields === []) {
            return $next($request);
        }

        $errors = [];
        foreach ($fields as $field) {
            $errors[$field] = 'Emojis are not allowed in this field.';
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Emojis are not allowed in form fields.',
                'errors' => $errors,
            ], 422);
        }

        return back()
            ->withInput($request->except(array_merge(self::SKIP_KEYS, array_keys($request->allFiles()))))
            ->withErrors($errors);
    }
}
