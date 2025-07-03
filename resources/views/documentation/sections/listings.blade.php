<!-- Listings Section -->
<section id="listings" class="mb-12">
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