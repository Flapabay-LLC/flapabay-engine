<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Http\Requests\StoreLanguageRequest;
use App\Http\Requests\UpdateLanguageRequest;
use App\Models\User;
use App\Models\UserLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{

        /**
     * Get all supported languages.
     *
     * @return JsonResponse
     */
    public function getSupportedLang(): JsonResponse
    {
        $languages = Language::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'message' => 'Supported languages retrieved successfully.',
            'data' => $languages,
        ]);
    }

    /**
     * Add a new supported language.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addSupportedLang(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:5',
            'name' => 'required|string|max:255',
        ]);

        $language = Language::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Language added successfully.',
            'data' => $language,
        ]);
    }

    /**
     * Set the user's default language.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setUserDefaultLang(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'user_id' => 'required',
            'language_code' => 'required|string|max:5',
        ]);

        // Store the user's preferred language
        $user = User::where('id', $request->input('user_id'))->first();
        $user->language_code = $request->input('language_code');
        $user->save();
        // Set the locale for the user session
        app()->setLocale($validated['language_code']);

        return response()->json([
            'success' => true,
            'message' => 'User default language updated successfully.',
        ]);
    }

    /**
     * Get the current language translations with pluralization.
     *
     * @return JsonResponse
     */
    public function getTranslationsWithPluralization(): JsonResponse
    {
        $translations = [
            'welcome' => trans('messages.welcome'),
            'items' => trans_choice('messages.items', 5, ['count' => 5]), // Using pluralization
        ];

        return response()->json([
            'success' => true,
            'message' => 'Translations retrieved successfully.',
            'data' => $translations,
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLanguageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Language $language)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Language $language)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLanguageRequest $request, Language $language)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Language $language)
    {
        //
    }
}
