<section id="host-signup" class="mb-8 p-4 bg-gray-900 text-white rounded-lg border-l-4 border-orange-500 shadow">
  <h2 class="mb-2 text-xl font-bold text-orange-300">Host Signup (registerHost)</h2>
  <p class="mb-2">
    The <code>/host/signup</code> endpoint allows a user to register as a host and create a listing and listing in a single request. This endpoint is handled by the <code>registerHost</code> method in <code>UserController.php</code>.
  </p>
  <h3 class="mb-2 text-lg font-semibold text-orange-200">Endpoint</h3>
  <pre class="bg-gray-800 text-orange-200 p-2 rounded text-xs overflow-x-auto">POST /api/host/signup</pre>
  <h3 class="mb-2 text-lg font-semibold text-orange-200">Request Payload</h3>
  <p class="mb-2">Send a <strong>multipart/form-data</strong> request with the following fields:</p>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left border border-gray-700 bg-gray-800 rounded mb-4">
      <thead>
        <tr>
          <th class="px-4 py-2 border-b border-gray-700">Field</th>
          <th class="px-4 py-2 border-b border-gray-700">Type</th>
          <th class="px-4 py-2 border-b border-gray-700">Description</th>
        </tr>
      </thead>
      <tbody>
        <tr><td class="px-4 py-2">type_of_place</td><td class="px-4 py-2">string</td><td class="px-4 py-2">Type of place (e.g., "entire_place", "room", "shared_room_in_a_hostel")</td></tr>
        <tr><td class="px-4 py-2">address</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Location details (coordinates, state, street, postal code, etc.)</td></tr>
        <tr><td class="px-4 py-2">coordinates</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Location pin on the map</td></tr>
        <tr><td class="px-4 py-2">guests</td><td class="px-4 py-2">integer</td><td class="px-4 py-2">Maximum number of guests (saved as <code>maximum_guests</code>)</td></tr>
        <tr><td class="px-4 py-2">bedrooms</td><td class="px-4 py-2">integer</td><td class="px-4 py-2">Number of bedrooms (saved as <code>num_of_bedrooms</code>)</td></tr>
        <tr><td class="px-4 py-2">bathrooms</td><td class="px-4 py-2">integer</td><td class="px-4 py-2">Number of bathrooms (saved as <code>num_of_bathrooms</code>)</td></tr>
        <tr><td class="px-4 py-2">every_bedroom_has_lock</td><td class="px-4 py-2">boolean</td><td class="px-4 py-2">Does every bedroom have a lock? (1 = yes, 0 = no)</td></tr>
        <tr><td class="px-4 py-2">kind_of_bathrooms</td><td class="px-4 py-2">string</td><td class="px-4 py-2">Types of bathrooms accessible to guests</td></tr>
        <tr><td class="px-4 py-2">who_is_there</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Who else might be present (e.g., "me", "my family", "other guests")</td></tr>
        <tr><td class="px-4 py-2">favourites</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Guest favorites related to the place</td></tr>
        <tr><td class="px-4 py-2">safety_items</td><td class="px-4 py-2">string</td><td class="px-4 py-2">Safety items (e.g., smoke alarm, first aid kit)</td></tr>
        <tr><td class="px-4 py-2">images</td><td class="px-4 py-2">array of files</td><td class="px-4 py-2">Photos of the house (max 5 images)</td></tr>
        <tr><td class="px-4 py-2">title</td><td class="px-4 py-2">string</td><td class="px-4 py-2">Title for the house listing</td></tr>
        <tr><td class="px-4 py-2">features</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Features of the house (e.g., "peaceful", "unique")</td></tr>
        <tr><td class="px-4 py-2">description</td><td class="px-4 py-2">string</td><td class="px-4 py-2">Detailed description of the place</td></tr>
        <tr><td class="px-4 py-2">host_booking_settings</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Booking settings for the place</td></tr>
        <tr><td class="px-4 py-2">who_to_welcome_first_reservation</td><td class="px-4 py-2">string</td><td class="px-4 py-2">Who to welcome for the first reservation</td></tr>
        <tr><td class="px-4 py-2">weekday_price</td><td class="px-4 py-2">decimal</td><td class="px-4 py-2">Base price for weekdays</td></tr>
        <tr><td class="px-4 py-2">weekend_price</td><td class="px-4 py-2">decimal</td><td class="px-4 py-2">Base price for weekends</td></tr>
        <tr><td class="px-4 py-2">discounts</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Discounts applicable to the booking</td></tr>
        <tr><td class="px-4 py-2">place_items</td><td class="px-4 py-2">json</td><td class="px-4 py-2">Specific items available for guests</td></tr>
      </tbody>
    </table>
  </div>
  <h3 class="mb-2 text-lg font-semibold text-orange-200">Response</h3>
  <p class="mb-2">On success, returns the created user, listing, and listing data:</p>
  <pre class="bg-gray-800 text-orange-200 p-2 rounded text-xs overflow-x-auto">{
  "status": "success",
  "message": "Host registered and listing/listing created successfully",
  "data": {
    "user_id": 1,
    "user_id": "1234",
    "listing": { ... },
    "listing": { ... }
  }
}</pre>
  <p class="text-orange-200 text-sm mt-2">
    <strong>Note:</strong> Fields like <code>guests</code>, <code>bedrooms</code>, and <code>bathrooms</code> are mapped to <code>maximum_guests</code>, <code>num_of_bedrooms</code>, and <code>num_of_bathrooms</code> in the database, respectively.
  </p>
</section> 