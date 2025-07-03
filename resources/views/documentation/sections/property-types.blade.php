<!-- Fetch System Property Types Section -->
<section id="property-types" class="mb-12">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">Fetch System Property Types</h2>
  <img src="{{ asset('public/images/1.png') }}">
  <p>Returns a list of all property types supported by the platform.</p>
  <pre><code class="block p-2 mt-1 text-sm text-white bg-black rounded">GET https://{base_url}/property-types</code></pre>
  <h4 class="mt-2 font-semibold">Response Example</h4>
  <pre class="overflow-x-auto p-4 mt-2 text-sm bg-gray-100 rounded"><code>{
  "success": true,
  "message": "Property types retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Apartment",
      "icon": "home-2",
      "black_icon": "home-2-black.svg",
      "white_icon": "home-2-white.svg",
      "bg_color": "#F5F5F5",
      "type": "experience",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    },
    {
      "id": 2,
      "name": "Villa",
      "icon": "villa",
      "black_icon": "villa-black.svg",
      "white_icon": "villa-white.svg",
      "bg_color": "#FFFFFF",
      "type": "stay",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z",
      "updated_at": "2024-01-01T00:00:00Z"
    }
  ]
}
</code></pre>
  <h4 class="mt-4 font-semibold">Notes</h4>
  <ul class="ml-6 text-sm list-disc list-inside">
    <li>You can filter property types by <code>type</code> using a query parameter: <code class='text-white bg-black'>GET https://{base_url}/property-types?type=residential</code></li>
    <li>Each property type includes icon and color information for UI display.</li>
  </ul>
</section> 