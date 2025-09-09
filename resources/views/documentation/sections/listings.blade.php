<!-- Listings Section -->
<section id="listings" class="mb-12">
  <section class="p-4 mb-8 text-white bg-gray-900 rounded-lg border-l-4 border-orange-400 shadow">
    <span class="inline-block px-2 py-1 mb-4 font-mono text-sm text-white bg-black rounded">POST {base_url}/listings/create-new</span>
    <h3 class="mb-2 text-lg font-bold text-orange-300">Create New Listing (Wizard API)</h3>
    <p class="mb-2">
      The <code class="px-1 bg-gray-800 rounded">createNewListing</code> API allows hosts to create a property listing step-by-step (wizard style). Only <strong>one draft</strong> is allowed per host at a time. Each step updates the same draft record using the <code class="px-1 bg-gray-800 rounded">draft_id</code> and <code class="px-1 bg-gray-800 rounded">user_id</code> as keys. When all required fields are provided and <code class="px-1 bg-gray-800 rounded">finalize=1</code> is sent, the draft is finalized and a new draft can be started for that host.
    </p>
    <ul class="mb-2 ml-6 list-disc">
      <li><strong>user_id</strong> is required in every request.</li>
      <li>On the first step, omit <code class="px-1 bg-gray-800 rounded">draft_id</code> to create a new draft (if none exists for the host).</li>
      <li>On subsequent steps, send <code class="px-1 bg-gray-800 rounded">draft_id</code> to update the same draft.</li>
      <li>Each step can send any subset of fields (including arrays for amenities, house_rules, etc.).</li>
      <li>When <code class="px-1 bg-gray-800 rounded">finalize=1</code> is sent, all required fields must be present (either in the draft or the request).</li>
      <li>After finalization, a new draft can be started for the host.</li>
    </ul>
    <div class="mb-2">
      <strong class="text-orange-200">Example: Step Request</strong>
      <pre class="overflow-x-auto p-2 text-xs text-orange-200 bg-gray-800 rounded">POST /api/listings/create-listings
      {
        "user_id": "1111",
        "title": "Perfect Apartment Home"
      }
      // Response:
      {
        "draft_id": 42,
        "property": { ... }
      }</pre>
    </div>
    <div class="mb-2">
      <strong class="text-orange-200">Example: Finalize Request</strong>
      <pre class="overflow-x-auto p-2 text-xs text-orange-200 bg-gray-800 rounded">POST /api/listings/create-listings
{
  "user_id": "1111",
  "draft_id": 42,
  ... // all required fields
  "finalize": 1
}
// Response:
{
  "success": true,
  "property": { ... }
}</pre>
    </div>
    <p class="text-sm text-orange-200">If a required field is missing at finalize, the API will return a 422 error with details and the <code class="px-1 bg-gray-800 rounded">draft_id</code> for further updates.</p>
  </section>
  <div class="p-4 mb-6 bg-orange-100 border-l-4 border-orange-500">
    <strong>Note:</strong> <code>user_id</code> is required in every request to create a new listing. Only hosts can create listings, and the <code>user_id</code> must be valid and present in every step of the listing creation wizard.
  </div>
  <h2 class="mb-4 text-2xl font-bold text-orange-600">Fetch All Listings</h2>
  <p class="mb-4 text-base text-gray-700">
    Retrieve a list of all available listings. This endpoint is public and returns a paginated array of listings with their details.
  </p>
  <div class="mb-4">
    <span class="inline-block px-2 py-1 font-mono text-sm text-white bg-black rounded">GET https://{base_url}/listings</span>
  </div>
  <h3 class="mb-2 text-lg font-semibold text-orange-500">Request</h3>
  <ul class="mb-4 ml-6 list-disc text-gray-700">
    <li><span class="font-mono text-sm text-black">Method:</span> <span class="font-mono text-sm text-orange-600">GET</span></li>
    <li><span class="font-mono text-sm text-black">URL:</span> <span class="font-mono text-sm">https://{base_url}/listings</span></li>
    <li><span class="font-mono text-sm text-black">Auth:</span> <span class="font-mono text-sm text-orange-600">Not required</span></li>
  </ul>
  <h3 class="mb-2 text-lg font-semibold text-orange-500">Response</h3>
  <div class="mb-4">
    <span class="block mb-2 font-mono text-sm text-black">200 OK</span>
    <pre class="overflow-x-auto p-4 text-xs text-gray-800 bg-gray-100 rounded">
{
  "data": [
    {
      "id": 1,
      "title": "Apartment in City Center",
      "description": "A beautiful apartment close to all amenities...",
      "price": 120,
      "currency": "ZMW",
      "location": "Lusaka",
      "images": [
        "https://.../listing1.jpg",
        "https://.../listing2.jpg"
      ],
      // ... more fields ...
    },
    // ... more listings ...
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100
  }
}
    </pre>
  </div>
  <h3 class="mb-2 text-lg font-semibold text-orange-500">Notes</h3>
  <ul class="ml-6 list-disc text-gray-700">
    <li>Use pagination parameters <span class="font-mono text-xs text-black">?page=</span> and <span class="font-mono text-xs text-black">?per_page=</span> to navigate results.</li>
    <li>Returned fields may vary depending on listing details.</li>
  </ul>
</section> 