<section id="verification-status" class="mb-8 p-4 bg-gray-900 text-white rounded-lg border-l-4 border-orange-500 shadow">
  <h2 class="mb-2 text-xl font-bold text-orange-300">Verification Status</h2>
  <p class="mb-2">
    Many resources in the API use a <code class="bg-gray-800 px-1 rounded">verified</code> boolean field to indicate verification status. This field is typically used for users, emails, phone numbers, or other entities that require verification.
  </p>
  <div class="mb-4">
    <table class="min-w-full text-sm text-left border border-gray-700 bg-gray-800 rounded">
      <thead>
        <tr>
          <th class="px-4 py-2 border-b border-gray-700">Value</th>
          <th class="px-4 py-2 border-b border-gray-700">Meaning</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="px-4 py-2 border-b border-gray-700"><code>true</code> / <code>1</code></td>
          <td class="px-4 py-2 border-b border-gray-700">Verified</td>
        </tr>
        <tr>
          <td class="px-4 py-2"><code>false</code> / <code>0</code></td>
          <td class="px-4 py-2">Not verified (default)</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p class="text-orange-200 text-sm">
    <strong>Note:</strong> The <code>verified</code> field is always a boolean. <code>true</code> (or <code>1</code>) means the entity is verified. <code>false</code> (or <code>0</code>) means it is not verified (default).
  </p>
</section> 