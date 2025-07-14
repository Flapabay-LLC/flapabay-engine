# Chat Feature Double-Check Checklist

## Top-Level Goals
- Enable contextual, secure, role-aware messaging between guests and hosts.
- Ensure all chats are scoped within a clearly defined thread.
- Support real-time UX with fallback REST APIs.
- Prepare for future scale: analytics, automation, archived threads, moderation.

---

### 🧵 1. THREAD SYSTEM (FOUNDATIONAL)
- [x] Define thread model
- [x] Allow thread creation
- [x] Enforce thread-per-context
- [x] Add thread metadata
- [x] Fetch threads list
- [x] Archive / resolve thread
- [x] Role-based thread access
- [ ] Thread search
- [ ] Thread flags

### 💬 2. MESSAGE SYSTEM (PER THREAD)
- [x] Send message in thread
- [x] Retrieve messages
- [x] Delete message (me/both)
- [x] Prevent message w/o thread_id
- [ ] Edit message (optional)
- [ ] Reaction to messages (optional)

### 🔔 3. REAL-TIME EXPERIENCE (WebSockets / Hybrid)
- [x] Live message delivery
- [x] Typing indicators
- [x] Presence indicators
- [x] Read receipts
- [ ] Auto-reconnect and fallback
- [ ] Join/leave thread room

### 🧠 4. ROLE-AWARE ACTIONS (HOST FEATURES)
- [x] Saved replies
- [x] Pre-approvals
- [x] Special offers
- [x] Enforce role access
- [ ] Host automated replies

### 🖼️ 5. ATTACHMENTS & MEDIA
- [x] Send images/files
- [x] Preview media
- [x] Filter attachments per thread
- [ ] File types & limits

### 🛑 6. SYSTEM MESSAGES & AUTOMATION
- [x] Send system messages
- [x] System-generated threads
- [x] Label system vs user messages
- [ ] Scheduled messages
- [ ] AI summaries

### 🔐 7. SECURITY & PERMISSIONS
- [x] Only participants can read/send
- [x] Soft-deletion per user
- [x] Block/mute user (future)
- [ ] Message audit logs
- [ ] Abuse reporting

### 📊 8. ANALYTICS & ADMIN INSIGHTS
- [ ] Host response time tracking
- [ ] Thread resolution metrics
- [ ] Unread counts
- [ ] Admin dashboard

### 🧪 9. TESTING & QA PLAN
- [x] Unit tests
- [x] Integration tests
- [x] Permission tests
- [ ] Load tests
- [ ] Real-time socket test suite

### 🧼 10. CLEANUP & MIGRATION
- [x] Deprecate old /chats endpoints
- [x] Migrate all features to /chat/thread/...
- [ ] Frontend migration planning
- [ ] Documentation update

---

Refer to this checklist before each release or major change to ensure all critical chat features and requirements are covered. 

## Event Payloads & Channel Authorization

### Example Event Payloads

#### MessageSent
```
{
  "message": {
    "id": 123,
    "thread_id": 45,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "Hello!",
    "type": "text",
    "meta": {},
    "created_at": "2024-07-08T12:00:00Z"
  }
}
```

#### MessageRead
```
{
  "message_id": 123,
  "thread_id": 45,
  "user_id": 2
}
```

#### Typing
```
{
  "thread_id": 45,
  "user_id": 1,
  "is_typing": true
}
```

#### PresenceUpdated
```
{
  "user_id": 1,
  "is_online": true
}
```

### Channel Authorization
- All chat events are broadcast on private channels named by thread, e.g. `private-thread.45`.
- Only thread participants (guest or host) can subscribe to a thread's channel.
- Channel authorization is enforced in Laravel's BroadcastServiceProvider (see policies).
- Example: `PrivateChannel('thread.' . $threadId)`
- Presence events use `private-user.{userId}` for online/offline status.
- Security: Unauthorized users cannot subscribe to channels for threads they do not participate in. 