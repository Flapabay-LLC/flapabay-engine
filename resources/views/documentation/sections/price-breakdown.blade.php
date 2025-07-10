<section id="price-breakdown" class="mb-8 p-6 bg-white rounded-lg border border-gray-200 shadow-sm">
  <h2 class="mb-4 text-2xl font-bold text-gray-800">Price Breakdown Calculation</h2>
  
  <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
    <h3 class="mb-2 text-lg font-semibold text-blue-800">Overview</h3>
    <p class="text-blue-700">
      The price breakdown calculation follows Airbnb-style pricing with multiple components that are calculated per night and then totaled.
    </p>
  </div>

  <div class="mb-6">
    <h3 class="mb-3 text-xl font-semibold text-gray-700">Price Components</h3>
    
    <div class="space-y-4">
      <div class="p-4 bg-gray-50 rounded-lg">
        <h4 class="mb-2 font-semibold text-gray-800">1. Base Price</h4>
        <p class="text-gray-600 mb-2">The foundation cost for the stay</p>
        <code class="bg-gray-200 px-2 py-1 rounded text-sm">Base Price = Price per night × Number of nights</code>
      </div>

      <div class="p-4 bg-gray-50 rounded-lg">
        <h4 class="mb-2 font-semibold text-gray-800">2. Additional Guest Charges</h4>
        <p class="text-gray-600 mb-2">Extra cost for guests beyond the base guest count</p>
        <code class="bg-gray-200 px-2 py-1 rounded text-sm">Additional Guest Price = (Additional guests × Additional guest price per night) × Number of nights</code>
        <p class="text-sm text-gray-500 mt-1">Only applies if guests exceed the property's base guest count</p>
      </div>

      <div class="p-4 bg-gray-50 rounded-lg">
        <h4 class="mb-2 font-semibold text-gray-800">3. Children Charges</h4>
        <p class="text-gray-600 mb-2">Cost for children (if property charges for children)</p>
        <code class="bg-gray-200 px-2 py-1 rounded text-sm">Children Price = Number of children × Children price per night × Number of nights</code>
      </div>

      <div class="p-4 bg-gray-50 rounded-lg">
        <h4 class="mb-2 font-semibold text-gray-800">4. Pet Charges</h4>
        <p class="text-gray-600 mb-2">Cost for pets (if property allows pets)</p>
        <code class="bg-gray-200 px-2 py-1 rounded text-sm">Pet Price = Number of pets × $10 per pet per night × Number of nights</code>
        <p class="text-sm text-gray-500 mt-1">Standard $10 per pet per night fee</p>
      </div>

      <div class="p-4 bg-gray-50 rounded-lg">
        <h4 class="mb-2 font-semibold text-gray-800">5. Service Fee</h4>
        <p class="text-gray-600 mb-2">Platform service charge (Airbnb-style)</p>
        <code class="bg-gray-200 px-2 py-1 rounded text-sm">Service Fee = (Subtotal) × 12%</code>
        <p class="text-sm text-gray-500 mt-1">12% of the subtotal (base + additional guests + children + pets)</p>
      </div>
    </div>
  </div>

  <div class="mb-6">
    <h3 class="mb-3 text-xl font-semibold text-gray-700">Calculation Example</h3>
    
    <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
      <h4 class="mb-2 font-semibold text-green-800">Sample Scenario</h4>
      <ul class="text-sm text-green-700 space-y-1">
        <li>• Property: $100 per night</li>
        <li>• Stay: 3 nights</li>
        <li>• Guests: 4 (base is 2, so 2 additional)</li>
        <li>• Additional guest price: $20 per night</li>
        <li>• Children: 1 ($15 per night)</li>
        <li>• Pets: 1 ($10 per night)</li>
      </ul>
    </div>

    <div class="mt-4 p-4 bg-gray-100 rounded-lg">
      <h4 class="mb-2 font-semibold text-gray-800">Calculation Breakdown</h4>
      <div class="text-sm space-y-1">
        <p><strong>Base Price:</strong> $100 × 3 nights = $300</p>
        <p><strong>Additional Guests:</strong> 2 guests × $20 × 3 nights = $120</p>
        <p><strong>Children:</strong> 1 child × $15 × 3 nights = $45</p>
        <p><strong>Pets:</strong> 1 pet × $10 × 3 nights = $30</p>
        <p><strong>Subtotal:</strong> $300 + $120 + $45 + $30 = $495</p>
        <p><strong>Service Fee:</strong> $495 × 12% = $59.40</p>
        <p><strong>Total:</strong> $495 + $59.40 = $554.40</p>
      </div>
    </div>
  </div>

  <div class="mb-6">
    <h3 class="mb-3 text-xl font-semibold text-gray-700">API Response Structure</h3>
    
    <div class="p-4 bg-gray-100 rounded-lg">
      <pre class="text-sm overflow-x-auto"><code>{
  "status": "success",
  "data": {
    "price_breakdown": {
      "nights": 3,
      "base_price": 300.00,
      "additional_guest_price": 120.00,
      "children_price": 45.00,
      "pet_price": 30.00,
      "subtotal": 495.00,
      "service_fee": 59.40,
      "total": 554.40,
      "currency": "USD"
    }
  }
}</code></pre>
    </div>
  </div>

  <div class="mb-6">
    <h3 class="mb-3 text-xl font-semibold text-gray-700">Important Notes</h3>
    
    <div class="space-y-2 text-sm text-gray-600">
      <div class="flex items-start">
        <span class="mr-2 text-orange-500">•</span>
        <span>Additional guest charges only apply when guests exceed the property's base guest count</span>
      </div>
      <div class="flex items-start">
        <span class="mr-2 text-orange-500">•</span>
        <span>Children and pet charges are optional and depend on property settings</span>
      </div>
      <div class="flex items-start">
        <span class="mr-2 text-orange-500">•</span>
        <span>Service fee is calculated as 12% of the subtotal</span>
      </div>
      <div class="flex items-start">
        <span class="mr-2 text-orange-500">•</span>
        <span>All prices are rounded to 2 decimal places</span>
      </div>
      <div class="flex items-start">
        <span class="mr-2 text-orange-500">•</span>
        <span>Currency is inherited from the property settings</span>
      </div>
    </div>
  </div>

  <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
    <h4 class="mb-2 font-semibold text-yellow-800">Developer Tips</h4>
    <ul class="text-sm text-yellow-700 space-y-1">
      <li>• Use the <code class="bg-yellow-200 px-1 rounded">getPropertyAvailability</code> endpoint to preview pricing before creating a reservation</li>
      <li>• The price breakdown is stored with each reservation for historical accuracy</li>
      <li>• Guest capacity validation happens before price calculation</li>
      <li>• All date calculations use Carbon for timezone consistency</li>
    </ul>
  </div>
</section> 