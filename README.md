<p>git init</p>

<p>git remote add origin https://github.com/Flapabay-LLC/flapabay-engine.git</p>

<p>git fetch</p>

<p>git checkout master</p>

<p>php artisan migrate --seed</p>

<p>Finally go to your localhost project folder, <b>No Need to run php artisan serve</b><p>
2019
![1](https://github.com/user-attachments/assets/d38ff85d-0ae4-4b39-a476-7df81144ba8c)

## 📨 Chat API Documentation (Thread-Based)

### Authentication
All endpoints require authentication (JWT or session).

---

### 💬 Threads & Inbox

#### List Threads (REST)
**GET** `/api/v1/chat/threads`
- **Query Params:**
  - `category` (optional): Filter by dynamic category (e.g., `Homes`, `Experiences`, `Support`, `All`)
  - `per_page` (optional): Pagination size (default: 20)
  - `page` (optional): Page number
- **Returns:** Paginated list of threads (only those where user is guest or host)
- **Thread Object:**
  - `id`, `guest_id`, `host_id`, `category`, `thread_type`, `context_type`, `context_id`, `status`, `updated_at`, `guest`, `host`

#### Get Single Thread (REST)
**GET** `/api/v1/chat/thread/{id}`
- **Returns:** Thread object with all metadata, messages, and participants (if user is guest or host)

---

### ✉️ Messaging

#### Start New Thread (REST)
**POST** `/api/v1/chat/start`
- **Body:**
  - `host_id` (required)
  - `message` (required)
  - `listing_id` (optional)
  - `booking_id` (optional)
  - `category` (optional, fallback if no context)
- **Returns:** `{ status, message, thread_id, data: first_message }`
- **Category:** Dynamically resolved from listing/booking context or explicit input.

#### Send Message to Thread (REST + Socket)
**POST** `/api/v1/chat/thread/message`
- **Body:**
  - `thread_id` (required)
  - `message` (required)
- **Returns:** `{ status, message, data: message }`
- **Real-time:** Emits `MessageSent` event on socket.

#### Get Thread Messages (REST)
**GET** `/api/v1/chat/thread/{threadId}/messages`
- **Query Params:**
  - `per_page` (optional)
  - `page` (optional)
- **Returns:** Paginated messages for the thread, ordered by timestamp.

#### Delete Message (REST)
**DELETE** `/api/v1/chats/message/{messageId}/me` (for self only)
**DELETE** `/api/v1/chats/message/{messageId}/both` (for both parties, sender only)

---

### 💬 Typing & Presence

#### Typing Indicator (REST + Socket)
**POST** `/api/v1/chat/typing-status`
- **Body:**
  - `thread_id` (required)
  - `is_typing` (boolean)
- **Real-time:** Emits typing event to thread participants.

#### Presence Update (REST + Socket)
**POST** `/api/v1/user/presence`
- **Body:**
  - `is_online` (boolean)
- **Real-time:** Emits presence event to all chat partners.

---

### 📎 Attachments
- **Send as part of message** (see `meta` field in message object).
- **Attachment metadata:** `{ url, name, type, size }`
- **Validation:** Only supported file types allowed.

---

### ⚙️ Roles & Permissions
- **Guests** can only start threads with hosts.
- **Hosts** can only send pre-approvals/special offers.
- **All endpoints** enforce thread membership (guest or host).

---

### ✨ Special Offers / Pre-Approvals (REST)
**POST** `/api/v1/chat/send-pre-approval`
**POST** `/api/v1/chat/send-special-offer`
- **Body:**
  - `thread_id` (required)
  - `message` (required)
  - `meta` (optional, offer details)
- **Only hosts** can send.

---

### 📣 System Messages (REST + Socket)
- Sent as part of thread, with `type: system` and `meta` for event details.
- `sender_id` is always a real user (host or guest), never null.

---

### ✅ Test Checklist
- All endpoints return correct status codes and error responses.
- Filtering by dynamic category works (`?category=...`).
- Pagination, unread counts, and metadata are correct.
- Only thread members can access thread/messages.
- Real-time events (MessageSent, Typing, Presence) are emitted as expected.

---

**REST** = HTTP endpoint.  
**Socket** = Real-time event (broadcast via WebSockets, e.g., Laravel Echo/Pusher).

## Reverb Setup TODO

- [ ] 1. Check and update `.env` for broadcasting:
    - Set `BROADCAST_CONNECTION=reverb`
    - Set `REVERB_HOST=127.0.0.1`
    - Set `REVERB_PORT=8080`
    - Set `REVERB_SCHEME=http`
- [ ] 2. Ensure Reverb is installed via Composer and publish its config if needed.
- [ ] 3. Check `config/broadcasting.php` and `config/reverb.php` for correct settings (host, port, scheme, etc.).
- [ ] 4. Install/reinstall frontend dependencies (`laravel-echo`, `pusher-js`) and rebuild assets.
- [ ] 5. Update Echo configuration in `resources/js/bootstrap.js` (or similar) to use `broadcaster: 'reverb'` and correct host/port.
- [ ] 6. Start the Reverb server: `php artisan reverb:start`.
- [ ] 7. Start the Laravel backend server: `php artisan serve`.
- [ ] 8. Test frontend connection to Reverb: check browser console and network tab for WebSocket activity/errors.
- [ ] 9. Trigger a broadcast event in Laravel and verify it is received in the frontend and/or logged by Reverb.
