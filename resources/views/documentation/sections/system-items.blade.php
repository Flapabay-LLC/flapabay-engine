<!-- System Items Section -->
<section id="system-items" class="mb-12">
  <h2 class="mb-4 text-2xl font-bold text-orange-600">Fetch System Items to Use During Creating new Listing to be populated.</h2>
  <p class="mb-4 text-base text-gray-700">
    Retrieve lists of system-wide items such as amenities, favorites, place items, and listing types. These endpoints are public and help populate dropdowns, filters, and other UI elements.
  </p>
  <div class="mb-4 space-y-2">
    <span class="inline-block px-2 py-1 font-mono text-sm text-white bg-black rounded">GET https://{base_url}/system/amenities</span>
    <p class="mb-4 text-base text-gray-700">Include the amenity 'name' in the <b>amenities[]</b> array Input Field when creating a new listing.</p>
    <span class="inline-block px-2 py-1 font-mono text-sm text-white bg-black rounded">GET https://{base_url}/system/favorites</span>
    <p class="mb-4 text-base text-gray-700">Include the favorite 'name' in the <b>favorites[]</b> array Input Field when creating a new listing.</p>
    <span class="inline-block px-2 py-1 font-mono text-sm text-white bg-black rounded">GET https://{base_url}/system/place-items</span>
    <p class="mb-4 text-base text-gray-700">Include the place item 'name' in the <b>place_items[]</b> array Input Field when creating a new listing.</p>
    <span class="inline-block px-2 py-1 font-mono text-sm text-white bg-black rounded">GET https://{base_url}/system/listing-types</span>
    <p class="mb-4 text-base text-gray-700">Set the listing type 'Id' in the <b>listing_type_id</b> field when creating a new listing.</p>
    <span class="inline-block px-2 py-1 font-mono text-sm text-white bg-black rounded">GET https://{base_url}/categories</span>
    <p class="mb-4 text-base text-gray-700">Include the category 'Id' in the <b>category_id</b> array Input Field when creating a new listing.</p>
  </div>
  <h3 class="mb-2 text-lg font-semibold text-orange-500">Request</h3>
  <ul class="mb-4 ml-6 list-disc text-gray-700">
    <li><span class="font-mono text-sm text-black">Method:</span> <span class="font-mono text-sm text-orange-600">GET</span></li>
    <li><span class="font-mono text-sm text-black">Auth:</span> <span class="font-mono text-sm text-orange-600">Not required</span></li>
  </ul>
  <h3 class="mb-2 text-lg font-semibold text-orange-500">Response Example (Amenities)</h3>
  <pre class="overflow-x-auto p-4 text-xs text-gray-800 bg-gray-100 rounded">
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "WiFi",
      "icon": "wifi.svg"
    },
    {
      "id": 2,
      "name": "Parking",
      "icon": "parking.svg"
    }
    // ... more items ...
  ]
}
  </pre>
  <h3 class="mb-2 text-lg font-semibold text-orange-500">Notes</h3>
  <ul class="ml-6 list-disc text-gray-700">
    <li>Each endpoint returns a list of items relevant to its type (amenities, favorites, etc).</li>
    <li>Use these endpoints to populate UI elements like filters, dropdowns, and search options.</li>
    <li>Returned fields may vary depending on the item type.</li>
  </ul>
</section> 