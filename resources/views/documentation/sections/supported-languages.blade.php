<!-- Supported Languages Section -->
<section id="supported-languages" class="mb-12">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">Supported Languages</h2>
  <p class="mb-2">The API provides endpoints to fetch supported languages and set a user's default language.</p>
  <h3 class="mt-4 mb-2 text-lg font-semibold">Get Supported Languages</h3>
  <p>Returns a list of all active languages supported by the platform.</p>
  <pre><code class="block p-2 mt-1 text-sm text-white bg-black rounded">GET https://{base_url}/supported-lang</code></pre>
  <h4 class="mt-2 font-semibold">Response Example</h4>
  <pre class="overflow-x-auto p-4 mt-2 text-sm bg-gray-100 rounded"><code>{
  "success": true,
  "message": "Supported languages retrieved successfully.",
  "data": [
    {
      "id": 1,
      "code": "en",
      "name": "English",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "code": "fr",
      "name": "French",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ]
}
</code></pre>
  <h3 class="mt-6 mb-2 text-lg font-semibold">Set User Default Language</h3>
  <p>Sets the default language for a user. Requires authentication.</p>
  <pre><code class="block p-2 mt-1 text-sm text-white bg-black rounded">POST https://{base_url}/set-user-default-supported-lang</code></pre>
  <h4 class="mt-2 font-semibold">Request Body</h4>
  <ul class="ml-6 text-sm list-disc list-inside">
    <li><code>user_id</code> <span class="text-gray-500">(integer, required)</span> – The user's ID.</li>
    <li><code>language_code</code> <span class="text-gray-500">(string, required)</span> – The language code (e.g., <code>en</code>, <code>fr</code>).</li>
  </ul>
  <h4 class="mt-2 font-semibold">Response Example</h4>
  <pre class="overflow-x-auto p-4 mt-2 text-sm bg-gray-100 rounded"><code>{
  "success": true,
  "message": "User default language updated successfully."
}
</code></pre>
  <h4 class="mt-4 font-semibold">Notes</h4>
  <ul class="ml-6 text-sm list-disc list-inside">
    <li>The <code>language_code</code> must match one of the codes returned by <code>/supported-lang</code>.</li>
    <li>Changing the user's language will affect their session and localized content.</li>
  </ul>
</section> 